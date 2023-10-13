<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Console\ConfirmableTrait;
use CapsulesCodes\Population\Dumper;
use Illuminate\Console\View\Components\Warn;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Error;
use Exception;


class PopulateRollbackCommand extends Command implements Isolatable
{
    use ConfirmableTrait;

    protected $signature = 'populate:rollback';

    protected $description = 'Migration changes rollback';

    protected Dumper $dumper;


    public function __construct( Dumper $dumper )
    {
        parent::__construct();

        $this->dumper = $dumper;
    }

    public function handle() : int
    {
        $this->write( Warn::class, "The rollback command will only set back the latest copy of your database. You'll have to modify your migrations and models manually." );

        try
        {
            $this->dumper->revert();

            $this->write( Info::class, "Database copy successfully reloaded" );

            return 0;
        }
        catch( Exception $exception )
        {
            $this->write( Error::class, $exception->getMessage() );

            return 1;
        }
    }

    protected function write( $component, ...$arguments ) : void
    {
        ( new $component( $this->output ) )->render( ...$arguments );
    }
}
