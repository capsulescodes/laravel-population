<?php

namespace CapsulesCodes\Population\Providers;

use Illuminate\Support\ServiceProvider;
use CapsulesCodes\Population\Console\Commands\PopulateCommand;


class PopulationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom( __DIR__ . '/../../config/population.php', 'population' );

        if( $this->app->runningInConsole() ) $this->commands( [ PopulateCommand::class ] );
    }
}
