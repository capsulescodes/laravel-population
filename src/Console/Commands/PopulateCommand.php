<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use CapsulesCodes\Population\Dumper;
use CapsulesCodes\Population\Replicator;
use CapsulesCodes\Population\Populator;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\BulletList;
use CapsulesCodes\Population\Traits\WriteTrait;
use Illuminate\Support\Collection;

use function Laravel\Prompts\confirm;



class PopulateCommand extends BaseCommand
{
    use WriteTrait;

    protected $signature = "populate";

    protected $description = "Manage your database using prompts";


    public function __construct( Dumper $dumper, Replicator $replicator, Populator $populator )
    {
        parent::__construct();

        $this->dumper = $dumper;
        $this->migrator = $replicator;
        $this->populator = $populator;

    }


    public function handle()
    {
        $this->uuid = Str::orderedUuid()->getHex()->serialize();

        $this->registerShutdownHandler();

        $this->migrator->usingConnection( null, function()
        {
            $this->dumper->setOutput( $this->output );
            $this->migrator->setOutput( $this->output );
            $this->populator->setOutput( $this->output );

            $this->dumper->copy();

            try
            {
                $this->migrator->replicate( $this->uuid, $this->migrator->getMigrationFiles( $this->getMigrationPaths() ) );

                $this->migrator->inspect( $this->uuid );

                $this->populate();

                return 0;
            }
            catch( Exception $e )
            {
                $this->migrator->clean( $this->uuid );

                $this->dumper->remove();

                throw $e;

                return 1;
            }
        });


        return 0;
    }

    protected function registerShutdownHandler()
    {
        register_shutdown_function( function()
        {
            if( ! $this->populator->isModified() )
            {
                $this->migrator->clean( $this->uuid );

                $this->dumper->remove();
            }
        });
    }


    protected function populate() : void
    {
        foreach( $this->migrator->getDirties() as $table => $changes )
        {
            $this->write( Info::class, "Table '{$table}' has changes" );

            $this->write( BulletList::class, $changes->map( fn( $change, $column ) => match( true )
            {
                ( $change[ 'old' ] && $change[ 'new' ] ) => "update column : '{$column}' => type : {$change[ 'old' ]} > {$change[ 'new' ]}",
                ( $change[ 'old' ] && ! $change[ 'new' ] ) => "delete column : '{$column}' => type : {$change[ 'old' ]}",
                ( ! $change[ 'old' ] && $change[ 'new' ] ) => "create column : '{$column}' => type : {$change[ 'new' ]}",
            } ) );

            $confirmed = confirm( "Do you want to proceed on populating the '{$table}' table?", false );

            if( $confirmed )
            {
                $this->populator->listen( $this->uuid, $this->migrator->getDirties() );
            }
            else
            {
                $this->migrator->clean( $this->uuid, Collection::make( [ $table => $this->migrator->getTables()->filter( fn( $value, $key ) => $key === $table )->first() ] ), true );
            }
        }

        if( $this->populator->isModified() ) $this->write( Info::class, 'Population succeeded.' );
    }
}
