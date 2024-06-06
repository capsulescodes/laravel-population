<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Console\ConfirmableTrait;
use CapsulesCodes\Population\Dumper;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\View\Components\Warn;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Error;
use Symfony\Component\Console\Input\InputOption;
use Exception;


class PopulateRollbackCommand extends Command implements Isolatable
{
    use ConfirmableTrait;


    protected $name = 'populate:rollback';
    protected $description = 'Migration changes rollback';

    protected Dumper $dumper;
    protected Replicator $replicator;

    protected int $status;


    public function __construct( Dumper $dumper, Replicator $replicator )
    {
        parent::__construct();

        $this->dumper = $dumper;
        $this->migrator = $replicator;
    }

    public function handle() : int
    {
        $this->write( Warn::class, "The rollback command will only set back the latest copy of your database(s). You'll have to modify your migrations and models manually." );

        $this->status = 0;

        $databases = Collection::make( empty( $this->input->getOption( 'database' ) ) ? [ Config::get( 'database.default' ) ] : $this->input->getOption( 'database' ) ) ;

        $databases->each( function( $database ) use ( $databases )
        {
            if( $this->status ) return;

            if( $databases->count() > 1 ) $this->write( Info::class, "Rolling back {$database} database..." );

            $this->migrator->usingConnection( $database, function() use ( $database )
            {
                $this->migrator->setOutput( $this->output );

                try
                {
                    $this->migrator->databaseExists( $database );

                    $this->dumper->revert( $database );

                    $this->migrator->resolveConnection( $database )->reconnect();

                    $this->write( Info::class, "Database dump successfully reloaded" );
                }
                catch( Exception $exception )
                {
                    $this->write( Error::class, $exception->getMessage() );

                    $this->status = 1;
                }
            } );
        } );

        return $this->status;
    }

    protected function write( $component, ...$arguments ) : void
    {
        ( new $component( $this->output ) )->render( ...$arguments );
    }


    protected function getOptions() : array
    {
        return [
            [ 'database', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The database connection(s) to be inspected' ]
        ];
    }
}
