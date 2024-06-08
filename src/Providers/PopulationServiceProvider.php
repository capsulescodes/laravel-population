<?php

namespace CapsulesCodes\Population\Providers;

use CapsulesCodes\Population\Console\Commands\PopulateCommand;
use CapsulesCodes\Population\Console\Commands\PopulateRollbackCommand;
use CapsulesCodes\Population\Parser;
use CapsulesCodes\Population\Replicator;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;


class PopulationServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->singleton( Parser::class, fn() => new Parser( new ParserFactory(), new NodeTraverser(), new Standard() ) );
        $this->app->singleton( Replicator::class, fn( Application $app ) => new Replicator( $app[ 'migrator' ], $app[ Parser::class ] ) );
    }

    public function boot() : void
    {
        $this->mergeConfigFrom( __DIR__ . '/../../config/population.php', 'population' );

        if( $this->app->runningInConsole() ) $this->commands( [ PopulateCommand::class, PopulateRollbackCommand::class ] );
    }
}
