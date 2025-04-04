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





it( 'can replicate existing migrations on a specific PostgreSQL database', function() : void
{
    [ $base, $new ] = replicateMigrationsOnPostgreSQLDatabase( 'two' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );
} );


it( 'can replicate existing migrations on multiple specific PostgreSQL databases', function() : void
{
    [ $base, $new ] = replicateMigrationsOnPostgreSQLDatabase( 'one' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );

    $this->replicator->clean();

    [ $base, $new ] = replicateMigrationsOnPostgreSQLDatabase( 'two' );

    $diff = $new->diff( $base );

    expect( $diff->count() )->toBe( 1 );

    expect( Str::length( $diff->first() ) )->toBe( Str::length( 'foo' ) );
} );




function replicateMigrationsOnPostgreSQLDatabase( string $database ) : array
{
    test()->replicator->setConnection( $database );

    test()->loadMigrationsFrom( 'tests/App/Database/Migrations/Databases/one/base' );

    $schema = Schema::connection( test()->replicator->getConnection() )->getCurrentSchemaName();

    $base = Collection::make( Schema::getTables( $schema ) )->pluck( 'name' );

    test()->replicator->path( 'tests/App/Database/Migrations/Databases/one/new/foo_table.php' );

    test()->replicator->replicate( test()->replicator->getMigrationFiles( test()->replicator->paths() ) );

    $new = Collection::make( Schema::getTables( $schema ) )->pluck( 'name' );

    test()->artisan( 'migrate:fresh' );

    return [ $base, $new ];
}
