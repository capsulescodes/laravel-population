<?php

use Illuminate\Support\Str;
use CapsulesCodes\Population\Replicator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;


beforeEach( function()
{
    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ) );
} );

afterEach( function()
{
    $this->replicator->clean( $this->uuid );
} );




it( 'can replicate existing migrations on a specific SQLite database', function()
{
    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );

} );


it( 'can replicate existing migrations on multiple specific SQLite databases', function()
{
    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'one' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );

    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


function replicateMigrationsOnMariaDBDatabase( string $database ) : array
{
    Config::set( 'database.default', $database );

    test()->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $base = Arr::pluck( Schema::getTables(), 'name' );

    test()->replicator->path( 'tests/app/database/migrations/one-database/new/foo_table.php' );

    test()->replicator->replicate( $database, test()->uuid, test()->replicator->getMigrationFiles( test()->replicator->paths() ) );

    $new = Arr::pluck( Schema::getTables(), 'name' );

    test()->artisan( 'migrate:fresh' );

    return [ $base, $new ];
}
