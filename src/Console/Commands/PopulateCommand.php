<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use CapsulesCodes\Population\Dumper;
use CapsulesCodes\Population\Replicator;
use CapsulesCodes\Population\Populator;
use Illuminate\Support\Str;
use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Support\Collection;
use Exception;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;



class PopulateCommand extends BaseCommand
{
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
            $this->migrator->setOutput( $this->output );

            if( ! $this->dumper->copy() )
            {
                $this->write( Error::class, "An error occurred when dumping your database. Verify your credentials." );

                return 1;
            }

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
            if( ! $this->populator->isDirty() )
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
                $this->request( $table, $changes );
            }
            else
            {
                $this->migrator->clean( $this->uuid, Collection::make( [ $table => $this->migrator->getTables()->filter( fn( $value, $key ) => $key === $table )->first() ] ), true );
            }
        }

        if( $this->populator->isDirty() ) $this->write( Info::class, 'Population succeeded.' );
    }

    protected function request( $table, $changes ) : void
    {
        $formulas = Collection::make();

        $records = $this->load( $table );



        if( $records->isEmpty() )
        {
            $this->write( Info::class, "The '{$table}' table columns have been updated but it seems the table has no records. Skipping record conversion" );
        }
        else
        {
            foreach( $changes as $column => $change )
            {
                if( $change[ 'new' ] )
                {
                    $input = text( "How would you like to convert the records for the column '{$column}' of type '{$change[ 'new' ]}'?", 'fn( $value, $model ) => $value' );

                    preg_match( '/^\s*fn\s*\(\s*(\$[\w\d]*\s*(?:,\s*\$[\w\d]*)?)?\s*\)\s*=>\s*(.+)\s*/', $input, $matches );

                    if( Collection::make( $matches )->isEmpty() )
                    {
                        $this->write( Error::class, "The function did not respect the required format" );

                        exit();
                    }

                    $formulas[ $column ] = $matches;
                }
                else
                {
                    $formulas[ $column ] = null;
                }
            }
        }

        $this->populator->process( $table, $this->uuid, $formulas, $records );
    }

    protected function load( $table = null, $input = null ) : Collection
    {
        $class = $input ?? 'App\\Models\\' . Str::studly( Str::singular( $table ) ) ;

        try
        {
            class_exists( $class );
        }
        catch( Exception )
        {
            if( ! $input ) $this->load( null, text( "The '{$class}' model path does not exist, please provide the correct path", "App\\Models\\" ) );

            $this->write( Error::class, "The model file was not found" );

            exit();
        }

        return $class::all();
    }

    protected function write( $component, ...$arguments )
    {
        ( new $component( $this->output ) )->render( ...$arguments );
    }
}
