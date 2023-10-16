<?php

use Illuminate\Support\Str;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;


beforeEach( function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ) );
});

afterEach( function()
{
    $this->replicator->clean( $this->uuid );
});




it( "throws an error if a migration does not contain a '\$name' property", function()
{
    $this->replicator->path( 'tests/app/database/migrations/base/foo_table.php' );

    expect( fn() => $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) ) )->toThrow( Exception::class );
});


it( 'can replicate existing migrations', function()
{
    $base = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    $this->replicator->path( 'tests/app/database/migrations/new/foo_table.php' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $new = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    expect( $new->toArray() )->toContain( "foo{$this->uuid}", ...$base->toArray() );
});


it( 'can determine no changes occurred in migrations', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new/qux_table' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties() )->toBeEmpty();
});


it( 'can list modified migrations table from database', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->keys() )->toContain( 'foo' );
});


it( 'can determine if foo column has no changes', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->not()->toHaveKey( 'foo' );
});


it( 'can determine if bar column has been added', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'bar' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'old', null );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'bar' ] )->toHaveKey( 'new' );
});


it( 'can determine if baz column has been removed', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'baz' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'old' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'baz' ] )->toHaveKey( 'new', null );
});


it( 'can determine if qux column type has been modified', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->get( 'foo' ) )->toHaveKey( 'qux' );

    expect( $this->replicator->getDirties()->get( 'foo' )[ 'qux' ] )->toHaveKeys( [ 'old', 'new' ] );
});
