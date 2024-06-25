<?php

use CapsulesCodes\Population\Parser;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;


beforeEach( function() : void
{
    $this->replicator = new Replicator( App::make( 'migrator' ), App::make( Parser::class ) );

    $this->replicator->setConnection( Config::get( 'database.default' ) );

    $this->loadMigrationsFrom( 'tests/App/Database/Migrations/Databases/one/base' );
} );

afterEach( function() : void
{
    $this->replicator->clean();

    $this->artisan( 'migrate:fresh' );
} );




it( 'can replicate existing migrations', function() : void
{
    $base = Collection::make( Schema::getTables() )->pluck( 'name' );

    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new/foo_table.php' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $new = Collection::make( Schema::getTables() )->pluck( 'name' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );
} );


it( 'can determine no changes occurred in migrations', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new/qux_table.pphp' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties() )->toBeEmpty();
} );


it( 'can list modified migrations table from database', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties()->keys() )->toContain( 'default.foo' );
} );


it( 'can determine if foo column has no changes', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties()->get( 'default.foo' ) )->not()->toHaveKey( 'foo' );
} );


it( 'can determine if bar column has been added', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties()->get( 'default.foo' ) )->toHaveKey( 'bar' );

    expect( $this->replicator->getDirties()->get( 'default.foo' )[ 'bar' ] )->toHaveKey( 'old', null );

    expect( $this->replicator->getDirties()->get( 'default.foo' )[ 'bar' ] )->toHaveKey( 'new' );
} );


it( 'can determine if baz column has been removed', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties()->get( 'default.foo' ) )->toHaveKey( 'baz' );

    expect( $this->replicator->getDirties()->get( 'default.foo' )[ 'baz' ] )->toHaveKey( 'old' );

    expect( $this->replicator->getDirties()->get( 'default.foo' )[ 'baz' ] )->toHaveKey( 'new', null );
} );


it( 'can determine if qux column type has been modified', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new' );

    $this->replicator->replicate( $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect();

    expect( $this->replicator->getDirties()->get( 'default.foo' ) )->toHaveKey( 'qux' );

    expect( $this->replicator->getDirties()->get( 'default.foo' )[ 'qux' ] )->toHaveKeys( [ 'old', 'new' ] );
} );


it( 'can not replicate migration without table names', function() : void
{
    $this->replicator->path( 'tests/App/Database/Migrations/Parser/BarTable.php' );

    $files = $this->replicator->getMigrationFiles( $this->replicator->paths() );

    expect( fn() => $this->replicator->replicate( $files ) )->toThrow( Exception::class );
} );
