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




it( 'can replicate existing migrations on a specific MariaDB database', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


it( 'can replicate existing migrations on multiple specific MariaDB databases', function() : void
{
    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'one' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );

    [ $base, $new ] = replicateMigrationsOnMariaDBDatabase( 'two' );

    expect( $new )->toContain( "foo-{$this->uuid}", ...$base );
} );


function replicateMigrationsOnMariaDBDatabase( string $database ) : array
{
    Config::set( 'database.default', $database );

    test()->loadMigrationsFrom( realpath( 'tests/app/database/migrations/databases/one/base' ) );

    $base = Arr::pluck( Schema::getTables(), 'name' );

    test()->replicator->path( realpath( 'tests/app/database/migrations/databases/one/new/foo_table.php' ) );

    test()->replicator->replicate( $database, test()->uuid, test()->replicator->getMigrationFiles( test()->replicator->paths() ) );

    $new = Arr::pluck( Schema::getTables(), 'name' );

    test()->artisan( 'migrate:fresh' );

    return [ $base, $new ];
}
