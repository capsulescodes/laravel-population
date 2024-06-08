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
    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ), App::make( Parser::class ) );
} );

afterEach( function() : void
{
    $this->replicator->clean( $this->uuid );
} );




it( 'can replicate existing migrations on a specific MySQL database', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


it( 'can replicate existing migrations on multiple specific MySQL databases', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'one' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );

    [ $base, $new ] = replicateMigrationsOnMySQLDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


function replicateMigrationsOnMySQLDatabase( string $database ) : array
{
    Config::set( 'database.default', $database );

    test()->loadMigrationsFrom( './tests/app/database/migrations/databases/one/base' );

    $base = Arr::pluck( Schema::getTables(), 'name' );

    test()->replicator->path( './tests/app/database/migrations/databases/one/new/foo_table.php' );

    test()->replicator->replicate( $database, test()->uuid, test()->replicator->getMigrationFiles( test()->replicator->paths() ) );

    $new = Arr::pluck( Schema::getTables(), 'name' );

    test()->artisan( 'migrate:fresh' );

    return [ $base, $new ];
}
