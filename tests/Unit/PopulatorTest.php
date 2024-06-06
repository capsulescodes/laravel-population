<?php

use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use Illuminate\Support\Str;
use CapsulesCodes\Population\Replicator;
use CapsulesCodes\Population\Populator;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo as BaseFoo;
use CapsulesCodes\Population\Tests\App\Models\New\Foo as NewFoo;
use Illuminate\Support\Collection;


beforeEach( function()
{
    $this->database = Config::get( 'database.default' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ) );


    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->seed( FooSeeder::class );

    $this->bases = BaseFoo::all();

    $this->replicator->path( 'tests/app/database/migrations/one-database/new/foo_table.php' );

    $this->replicator->replicate( Config::get( 'database.default' ), $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->populator = new Populator();

    $this->records = NewFoo::all();

    $this->formulas = Collection::make( [ "baz" => null, "bar" => [ "", "", "\"\"" ], "qux" => [ "", "", "\"\"" ] ] );
} );




it( 'is dirty after a population occurred', function()
{
    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    expect( $this->populator->isDirty() )->toBeTrue();
} );


it( 'can delete a column', function()
{
    $this->bases->each( function( $base ) { expect( $base->getAttribute( 'baz' ) )->not()->toBeNull(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) { expect( $new->getAttribute( 'baz' ) )->toBeNull(); } );
} );


it( 'can populate a new column', function()
{
    $this->bases->each( function( $base ) { expect( $base->getAttribute( 'bar' ) )->toBeNull(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) { expect( $new->getAttribute( 'bar' ) )->not()->toBeNull(); } );
} );


it( 'can populate a modified column', function()
{
    $this->bases->each( function( $base ) { expect( $base->getAttribute( 'qux' ) )->toBeInt(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) { expect( $new->getAttribute( 'qux' ) )->toBeString(); } );
} );
