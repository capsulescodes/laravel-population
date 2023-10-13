<?php

use Illuminate\Support\Str;
use CapsulesCodes\Population\Purgator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;


beforeEach( function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->purgator = new Purgator( App::make( 'migrator' ) );
});




it( 'can purge existing migrations', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/base' );

    $base = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    expect( $base->toArray() )->not()->toBeEmpty();

    $this->purgator->path( 'tests/app/database/migrations/base' );

    $this->purgator->purge( $this->purgator->getMigrationFiles( $this->purgator->paths() ) );

    $new = Collection::make( Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() );

    expect( $new->toArray() )->toContain( 'migrations' );
});
