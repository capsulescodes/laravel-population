<?php

use CapsulesCodes\Population\Parser;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


beforeEach( function() : void
{
    $this->database = Config::get( 'database.default' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ), App::make( Parser::class ) );

    $this->loadMigrationsFrom( realpath( 'tests/app/database/migrations/databases/one/base' ) );
} );

afterEach( function() : void
{
    $this->replicator->clean( $this->uuid );

    $this->artisan( 'migrate:fresh' );
} );




it( "returns en exception if current database doesn't exist", function() : void
{
    expect( fn() => $this->replicator->databaseExists( 'foo' ) )->toThrow( Exception::class );
} );


it( 'can replicate existing migrations', function() : void
{
    $base = Arr::pluck( Schema::getTables(), 'name' );

    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new/foo_table.php' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $new = Arr::pluck( Schema::getTables(), 'name' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


it( 'can determine no changes occurred in migrations', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new/qux_table' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties() )->toBeEmpty();
} );


it( 'can list modified migrations table from database', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->keys() )->toContain( 'foo' );
} );


it( 'can determine if foo column has no changes', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->not()->toHaveKey( 'foo' );
} );


it( 'can determine if bar column has been added', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'bar' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'old', null );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'new' );
} );


it( 'can determine if baz column has been removed', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'baz' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'old' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'new', null );
} );


it( 'can determine if qux column type has been modified', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new' ) );

    $this->replicator->replicate( $this->database, $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->database, $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'qux' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'qux' ] )->toHaveKeys( [ 'old', 'new' ] );
} );


it( 'can not replicate migration without table names', function() : void
{
    $this->replicator->path( realpath( 'tests/app/database/migrations/parser/foo_table.php' ) );

    $files = $this->replicator->getMigrationFiles( $this->replicator->paths() );

    expect( fn() => $this->replicator->replicate( $this->database, $this->uuid, $files ) )->toThrow( Exception::class );
} );
