<?php

use CapsulesCodes\Population\Parser;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use CapsulesCodes\Population\Models\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use CapsulesCodes\Population\Visitors\Reader;


beforeEach( function() : void
{
    $this->parser = new Parser( new ParserFactory(), new NodeTraverser(), new Standard() );
} );




it( 'can find one string table name in migration file', function() : void
{
    $schema = $this->parser->getSchemas( 'tests/App/Database/Migrations/Parser/foo_table.php' )->first();

    expect( $schema->connection )->toBeNull();

    expect( $schema->table )->toBe( 'foo' );
} );


it( 'can not find string table name variable in migration file', function() : void
{
    $schema = $this->parser->getSchemas( 'tests/App/Database/Migrations/Parser/BarTable.php' );

    expect( $schema )->toBeEmpty();
} );


it( 'can find multiple string table names in migration file', function() : void
{
    $schemas = $this->parser->getSchemas( 'tests/App/Database/Migrations/Parser/baz_table.php' );

    expect( $schemas->first()->connection )->toBeNull();

    expect( $schemas->first()->table )->toBe( 'baz' );

    expect( $schemas->last()->connection )->toBeNull();

    expect( $schemas->last()->table )->toBe( 'qux' );
} );


it( 'can find multiple connection and table names in migration file', function() : void
{
    $schemas = $this->parser->getSchemas( 'tests/App/Database/Migrations/Parser/qux_table.php' );

    expect( $schemas->first()->connection )->toBe( 'qux' );

    expect( $schemas->first()->table )->toBe( 'quux' );

    expect( $schemas->last()->connection )->toBeNull();

    expect( $schemas->last()->table )->toBe( 'corge' );
} );


it( 'can find unique connection and table names in migration file', function() : void
{
    $schemas = $this->parser->getSchemas( 'tests/App/Database/Migrations/Parser/quux_table.php' );

    expect( $schemas->first()->connection )->toBe( 'corge' );

    expect( $schemas->first()->table )->toBe( 'grault' );

    expect( $schemas->last()->connection )->toBe( 'quux' );

    expect( $schemas->last()->table )->toBe( 'garply' );
} );


it( 'can overwrite anonymous class migrations', function() : void
{
    $schema = new Schema( null, 'foo', 'tests/App/Database/Migrations/Parser/foo_table.php' );

    $migration = $this->parser->resolveMigration( $schema->file, Collection::make( [ $schema ] ) );

    $class = new ReflectionClass( get_class( $migration ) );

    expect( $class->isSubclassOf( Migration::class ) )->toBeTrue();

    expect( $class->isAnonymous() )->toBeTrue();
} );


it( 'can overwrite named class migrations', function() : void
{
    $migration = $this->parser->resolveMigration( 'tests/App/Database/Migrations/Parser/BarTable.php', Collection::make( [ ] ) );

    $class = new ReflectionClass( get_class( $migration ) );

    expect( $class->isSubclassOf( Migration::class ) )->toBeTrue();

    expect( Str::of( $class->name )->explode( '\\' )->last() )->toBe( 'BarTable' );
} );


it( 'can overwrite schema creation with given class', function() : void
{
    $schema = new Schema( null, 'corge', 'tests/App/Database/Migrations/Parser/qux_table.php' );

    $ast = $this->parser->getMigration( $schema->file, Collection::make( [ $schema ] ) );

    $visitor = new Reader();

    $this->parser->traverse( $ast, $visitor );

    expect( $visitor->getData() )->toBe( [ [ 'connection' => null, 'table' => $schema->code ] ] );
} );

it( 'can overwrite schema creation with given class and connection', function() : void
{
    $schema = new Schema( 'qux', 'quux', 'tests/App/Database/Migrations/Parser/qux_table.php' );

    $ast = $this->parser->getMigration( $schema->file, Collection::make( [ $schema ] ) );

    $visitor = new Reader();

    $this->parser->traverse( $ast, $visitor );

    expect( $visitor->getData() )->toBe( [ [ 'connection' => $schema->connection, 'table' => $schema->code ] ] );
} );

it( 'can overwrite schema creation with unknown class', function() : void
{
    $schema = new Schema( null, 'foo', 'tests/App/Database/Migrations/Parser/qux_table.php' );

    $ast = $this->parser->getMigration( $schema->file, Collection::make( [ $schema ] ) );

    $visitor = new Reader();

    $this->parser->traverse( $ast, $visitor );

    expect( $visitor->getData() )->toBe( [] );
} );

it( 'can overwrite schema creation with unknown connection', function() : void
{
    $schema = new Schema( null, 'qux', 'tests/App/Database/Migrations/Parser/qux_table.php' );

    $ast = $this->parser->getMigration( $schema->file, Collection::make( [ $schema ] ) );

    $visitor = new Reader();

    $this->parser->traverse( $ast, $visitor );

    expect( $visitor->getData() )->toBe( [] );
} );

it( 'can overwrite schema creation with connection outside schema creation', function() : void
{
    $schema = new Schema( 'quux', 'garply', 'tests/App/Database/Migrations/Parser/quux_table.php' );

    $ast = $this->parser->getMigration( $schema->file, Collection::make( [ $schema ] ) );

    $visitor = new Reader();

    $this->parser->traverse( $ast, $visitor );

    expect( $visitor->getData() )->toBe( [ [ 'connection' => $schema->connection, 'table' => $schema->code ] ] );
} );
