<?php

use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use Illuminate\Support\Facades\Config;


beforeEach( function()
{
    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

    $this->path = Config::get( 'population.path' );

    $this->parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/new' ];
});

afterEach( function()
{
    $this->disk->deleteDirectory( $this->path );
});





it( 'returns an error if the database connection is incorrect', function()
{
    $this->database = Config::get( 'database.connections.mysql.database' );

    Config::set( 'database.connections.mysql.database', 'no-package' );

    $this->artisan( 'populate' )
        ->expectsOutputToContain( "An error occurred when dumping your database. Verify your credentials." )
        ->assertExitCode( 1 );

    Config::set( 'database.connections.mysql.database', $this->database );
});


it( 'returns an error if the database has no migration', function()
{
    $this->artisan( 'populate' )
        ->expectsOutputToContain( "No tables migrated yet, please run artisan migrate." )
        ->assertExitCode( 1 );
});


it( 'closes gracefully if confirmation has been refused', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'No' )
        ->assertExitCode( 0 );
});


it( 'returns an error if the migration model does not exist', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsOutputToContain( "⇂ delete column : 'baz' => type : boolean  \n  ⇂ update column : 'qux' => type : integer > string  \n  ⇂ create column : 'bar' => type : string" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "App\\Models\\" )
        ->expectsOutputToContain( "The model file was not found." )
        ->assertExitCode( 1 );
});


it( 'updates the empty table columns without converting', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsOutputToContain( "⇂ delete column : 'baz' => type : boolean  \n  ⇂ update column : 'qux' => type : integer > string  \n  ⇂ create column : 'bar' => type : string" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsOutputToContain( "The 'foo' table columns have been updated but it seems the table has no records. Skipping record conversion." )
        ->assertExitCode( 0 );
});


it( 'updates the seeded table columns and recieves an incorrect conversion formula', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->seed( FooSeeder::class );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsOutputToContain( "⇂ delete column : 'baz' => type : boolean  \n  ⇂ update column : 'qux' => type : integer > string  \n  ⇂ create column : 'bar' => type : string" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'string'?", "foo" )
        ->expectsOutputToContain( "The function did not respect the required format." )
        ->assertExitCode( 1 );
});


it( 'updates the seeded table columns and populates successfully', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->seed( FooSeeder::class );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsOutputToContain( "⇂ delete column : 'baz' => type : boolean  \n  ⇂ update column : 'qux' => type : integer > string  \n  ⇂ create column : 'bar' => type : string" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'string'?", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'string'?", "fn() => fake()->sentence()" )
        ->expectsOutputToContain( "Population succeeded." )
        ->assertExitCode( 0 );
});
