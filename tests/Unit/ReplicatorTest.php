<?php

use Illuminate\Support\Str;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;


beforeEach( function()
{
    $this->database = Config::get( 'database.default' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ) );

    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );
} );

afterEach( function()
{
    $this->replicator->clean( $this->uuid );

    $this->artisan( 'migrate:fresh' );
} );




it( 'can replicate existing migrations', function()
{
    $base = Arr::pluck( Schema::getTables(), 'name' );

    $this->replicator->path( 'tests/app/database/migrations/one-database/new/foo_table.php' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $new = Arr::pluck( Schema::getTables(), 'name' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


it( 'can determine no changes occurred in migrations', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new/qux_table' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties() )->toBeEmpty();
} );


it( 'can list modified migrations table from database', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->keys() )->toContain( 'foo' );
} );


it( 'can determine if foo column has no changes', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->not()->toHaveKey( 'foo' );
} );


it( 'can determine if bar column has been added', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'bar' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'old', null );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'new' );
} );


it( 'can determine if baz column has been removed', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'baz' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'old' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'new', null );
} );


it( 'can determine if qux column type has been modified', function()
{
    $this->replicator->path( 'tests/app/database/migrations/one-database/new' );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'qux' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'qux' ] )->toHaveKeys( [ 'old', 'new' ] );
} );

it( "returns en exception if current database doesn't exist", function()
{
    expect( fn() => $this->replicator->databaseExists( 'foo' ) )->toThrow( Exception::class );
} );
