<?php

namespace CapsulesCodes\Population\Providers;

use Illuminate\Support\ServiceProvider;
use CapsulesCodes\Population\Replicator;
use Illuminate\Foundation\Application;
use CapsulesCodes\Population\Console\Commands\PopulateCommand;
use CapsulesCodes\Population\Console\Commands\PopulateRollbackCommand;

class PopulationServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->bind( Replicator::class, fn( Application $app ) => new Replicator( $app[ 'migrator' ] ) );
    }

    public function boot() : void
    {
        $this->mergeConfigFrom( __DIR__ . '/../../config/population.php', 'population' );

        if( $this->app->runningInConsole() ) $this->commands( [ PopulateCommand::class, PopulateRollbackCommand::class ] );
    }
}
