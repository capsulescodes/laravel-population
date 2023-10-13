<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use CapsulesCodes\Population\Dumper;
use CapsulesCodes\Population\Purgator;
use Illuminate\Console\View\Components\Warn;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Error;
use Exception;


class PopulateRollbackCommand extends BaseCommand
{
    protected $signature = 'populate:rollback';

    protected $description = 'Migration changes rollback';

    protected Dumper $dumper;
    protected Purgator $migrator;


    public function __construct( Dumper $dumper, Purgator $purgator )
    {
        parent::__construct();

        $this->dumper = $dumper;
        $this->migrator = $purgator;
    }

    public function handle() : int
    {
        $this->write( Warn::class, "The rollback command will only set back the latest copy of your database. You'll have to modify your migrations and models manually." );

        return $this->migrator->usingConnection( null, function()
        {
            $this->migrator->setOutput( $this->output );

            try
            {
                $this->dumper->revert();

                $this->migrator->purge( $this->migrator->getMigrationFiles( $this->getMigrationPaths() ) );

                $this->write( Info::class, "Database copy successfully reloaded" );

                return 0;
            }
            catch( Exception $exception )
            {
                $this->write( Error::class, $exception->getMessage() );

                return 1;
            }
        });
    }

    protected function write( $component, ...$arguments ) : void
    {
        ( new $component( $this->output ) )->render( ...$arguments );
    }
}
