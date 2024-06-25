<?php

use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Facades\App;
use CapsulesCodes\Population\Parser;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;


beforeEach( function() : void
{
    $this->replicator = new Replicator( App::make( 'migrator' ), App::make( Parser::class ) );
} );

afterEach( function() : void
{
    $this->replicator->clean();
} );




it( 'can replicate existing migrations on a specific MySQL database', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'two' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );
} );


it( 'can replicate existing migrations on multiple specific MySQL databases', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'one' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );

    $this->replicator->clean();

    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'two' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );
} );




function replicateMigrationsOnMySQLDatabase( string $database ) : array
{
    test()->replicator->setConnection( $database );

    test()->loadMigrationsFrom( 'tests/App/Database/Migrations/Databases/one/base' );

    $base = Collection::make( Schema::getTables() )->pluck( 'name' );

    test()->replicator->path( 'tests/App/Database/Migrations/Databases/one/new/foo_table.php' );

    test()->replicator->replicate( test()->replicator->getMigrationFiles( test()->replicator->paths() ) );

    $new = Collection::make( Schema::getTables() )->pluck( 'name' );

    test()->artisan( 'migrate:fresh' );

    return [ $base, $new ];
}
