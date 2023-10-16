<?php

use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo;


beforeEach( function()
{
    $this->parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/new' ];
});




it( 'returns an error if no dump left in directory', function()
{
    $this->artisan( 'populate:rollback' )
        ->expectsOutputToContain( "No database copy left in directory." )
        ->assertExitCode( 1 );
});


it( 'returns an error if the database connection is incorrect', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->seed( FooSeeder::class );

    $this->artisan( 'populate', $this->parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'string'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'string'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsOutputToContain( "Population succeeded." )
        ->assertExitCode( 0 );

    $database = Config::get( 'database.connections.mysql.database' );

    Config::set( 'database.connections.mysql.database', 'no-package' );

    $this->artisan( 'populate:rollback' )
        ->expectsOutputToContain( "An error occurred when setting back your database. Verify your credentials." )
        ->assertExitCode( 1 );

    Config::set( 'database.connections.mysql.database', $database );
});


it( 'rolls back the latest database dump', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->seed( FooSeeder::class );

    $first = Foo::all()->toArray();

    $this->artisan( 'populate', $this->parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'string'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'string'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsOutputToContain( "Population succeeded." )
        ->assertExitCode( 0 );

    $second = Foo::all()->toArray();

    expect( $first )->not()->toEqual( $second );

    $this->artisan( 'populate:rollback' )
        ->expectsOutputToContain( "The rollback command will only set back the latest copy of your database. You'll have to modify your migrations and models manually." )
        ->expectsOutputToContain( "Database copy successfully reloaded" )
        ->assertExitCode( 0 );

    $third = Foo::all()->toArray();

    expect( $first )->toEqual( $third );
});
