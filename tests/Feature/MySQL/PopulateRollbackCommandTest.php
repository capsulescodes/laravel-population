<?php

use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use CapsulesCodes\Population\Tests\App\Database\Seeders\QuuxSeeder;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo;
use CapsulesCodes\Population\Tests\App\Models\Base\Quux;
use Illuminate\Support\Facades\Config;


beforeEach( function() : void
{
    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );
} );

afterEach( function() : void
{
    $this->disk->deleteDirectory( Config::get( 'population.path' ) );

    $this->artisan( 'migrate:fresh' );
} );




it( 'returns an error if no dump left in directory', function() : void
{
    $this->artisan( 'populate:rollback' )
        ->expectsOutputToContain( 'No database dump left in directory.' )
        ->assertExitCode( 1 );
} );


it( 'returns an error if the database connection is incorrect', function() : void
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/databases/one/base' );

    $this->seed( FooSeeder::class );

    $parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/databases/one/new', '--database' => 'one' ];

    $this->artisan( 'populate', $parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", 'CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo' )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsOutputToContain( 'Population succeeded.' )
        ->assertExitCode( 0 );

    $parameters = [ '--database' => 'two' ];

    $this->artisan( 'populate:rollback', $parameters )
        ->expectsOutputToContain( 'No database dump left in directory.' )
        ->assertExitCode( 1 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'rolls back the latest database dump', function() : void
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/databases/one/base' );

    $this->seed( FooSeeder::class );

    $first = Foo::all()->toArray();

    $parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/databases/one/new' ];

    $this->artisan( 'populate', $parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", 'CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo' )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsOutputToContain( 'Population succeeded.' )
        ->assertExitCode( 0 );

    $second = Foo::all()->toArray();

    expect( $first )->not()->toEqual( $second );

    $this->artisan( 'populate:rollback' )
        ->expectsOutputToContain( "The rollback command will only set back the latest copy of your database(s). You'll have to modify your migrations and models manually." )
        ->expectsOutputToContain( 'Database dump successfully reloaded' )
        ->assertExitCode( 0 );

    $third = Foo::all()->toArray();

    expect( $first )->toEqual( $third );

    $this->artisan( 'migrate:fresh' );
} );


it( 'rolls back the latest database dumps on two databases', function() : void
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/databases/many/base' );

    $this->seed( [ FooSeeder::class, QuuxSeeder::class ] );

    $firstFoos = Foo::all()->toArray();
    $firstQuuxes = Quux::all()->toArray();

    $parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/databases/many/new', '--database' => [ 'one', 'two' ] ];

    $this->artisan( 'populate', $parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", 'CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo' )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsConfirmation( "Do you want to proceed on populating the 'quux' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Quux' model path does not exist, please provide the correct path.", 'CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Quux' )
        ->expectsQuestion( "How would you like to convert the records for the column 'waldo' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->expectsQuestion( "How would you like to convert the records for the column 'grault' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", 'fn() => fake()->sentence()' )
        ->assertExitCode( 0 );

    $secondFoos = Foo::all()->toArray();
    $secondQuuxes = Quux::all()->toArray();

    expect( $secondFoos )->not()->toEqual( $firstFoos );
    expect( $secondQuuxes )->not()->toEqual( $firstQuuxes );

    $parameters = [ '--database' => [ 'one', 'two' ] ];

    $this->artisan( 'populate:rollback', $parameters )
        ->expectsOutputToContain( "The rollback command will only set back the latest copy of your database(s). You'll have to modify your migrations and models manually." )
        ->expectsOutputToContain( 'Database dump successfully reloaded' )
        ->assertExitCode( 0 );

    $thirdFoos = Foo::all()->toArray();
    $thirdQuuxes = Quux::all()->toArray();

    expect( $thirdFoos )->toEqual( $firstFoos );
    expect( $thirdQuuxes )->toEqual( $firstQuuxes );

    $this->artisan( 'db:wipe', [ '--database' => 'two' ] );

    $this->artisan( 'migrate:fresh', [ '--database' => 'two' ] );
} );
