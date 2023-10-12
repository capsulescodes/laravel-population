<?php

use CapsulesCodes\Population\Tests\TestCase;
use Illuminate\Support\Str;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

uses( TestCase::class );

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



it( 'can replicate existing migrations', function()
{
    $base = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    $this->replicator->path( 'tests/app/database/migrations/new/foo_table.php' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $new = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    expect( $new->toArray() )->toContain( "foo{$this->uuid}", ...$base->toArray() );
});


it( 'can list each tables from database', function()
{
    $this->replicator->path( 'tests/app/database/migrations/new' );

    $this->replicator->replicate( $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->replicator->inspect( $this->uuid );

    expect( $this->replicator->getDirties()->keys() )->toContain( 'foo' , 'bar' );
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
