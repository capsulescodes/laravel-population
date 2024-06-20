<?php

use CapsulesCodes\Population\Parser;
use CapsulesCodes\Population\Populator;
use CapsulesCodes\Population\Replicator;
use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo as BaseFoo;
use CapsulesCodes\Population\Tests\App\Models\New\Foo as NewFoo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;


beforeEach( function() : void
{
    $this->database = Config::get( 'database.default' );

    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->replicator = new Replicator( App::make( 'migrator' ), App::make( Parser::class ) );

    $this->loadMigrationsFrom( 'tests/App/Database/Migrations/Databases/one/base' );

    $this->seed( FooSeeder::class );

    $this->bases = BaseFoo::all();

    $this->replicator->path( 'tests/App/Database/Migrations/Databases/one/new/foo_table.php' );

    $this->replicator->replicate( Config::get( 'database.default' ), $this->uuid, $this->replicator->getMigrationFiles( $this->replicator->paths() ) );

    $this->populator = new Populator();

    $this->records = NewFoo::all();

    $this->formulas = Collection::make( [ 'baz' => null, 'bar' => [ '', '', '""' ], 'qux' => [ '', '', '""' ] ] );
} );




it( 'is dirty after a population occurred', function() : void
{
    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    expect( $this->populator->isDirty() )->toBeTrue();
} );


it( 'can delete a column', function() : void
{
    $this->bases->each( function( $base ) : void {  expect( $base->getAttribute( 'baz' ) )->not()->toBeNull(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) : void { expect( $new->getAttribute( 'baz' ) )->toBeNull(); } );
} );


it( 'can populate a new column', function() : void
{
    $this->bases->each( function( $base ) : void { expect( $base->getAttribute( 'bar' ) )->toBeNull(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) : void { expect( $new->getAttribute( 'bar' ) )->not()->toBeNull(); } );
} );


it( 'can populate a modified column', function() : void
{
    $this->bases->each( function( $base ) : void { expect( $base->getAttribute( 'qux' ) )->toBeInt(); } );

    $this->populator->process( 'foo', $this->database, $this->uuid, $this->formulas, $this->records );

    $records = NewFoo::all();

    $records->each( function( $new ) : void { expect( $new->getAttribute( 'qux' ) )->toBeString(); } );
} );
