<?php

use CapsulesCodes\Population\Parser;
use Illuminate\Database\Migrations\Migration;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;


beforeEach( function() : void
{
    $this->uuid = Str::orderedUuid()->getHex()->serialize();

    $this->parser = new Parser( new ParserFactory(), new NodeTraverser(), new Standard() );
} );




it( 'can find one string table name in migration file', function() : void
{
    $data = $this->parser->getTables( realpath( 'tests/app/database/migrations/parser/bar_table.php' ) );

    expect( $data->toArray() )->toBe( [ 'bar' ] );
} );


it( 'can find multiple string table names in migration file', function() : void
{
    $data = $this->parser->getTables( realpath( 'tests/app/database/migrations/parser/baz_table.php' ) );

    expect( $data->toArray() )->toBe( [ 'baz', 'qux' ] );
} );


it( 'can not find string table name in migration file', function() : void
{
    $data = $this->parser->getTables( realpath( 'tests/app/database/migrations/parser/foo_table.php' ) );

    expect( $data->toArray() )->toBeEmpty();
} );


it( 'can overwrite anonymous class migrations', function() : void
{
    $migration = $this->parser->getMigration( realpath( 'tests/app/database/migrations/parser/baz_table.php' ), $this->uuid );

    $class = new ReflectionClass( get_class( $migration ) );

    expect( $class->isSubclassOf( Migration::class ) )->toBeTrue();
    expect( $class->isAnonymous() )->toBeTrue();
} );


it( 'can overwrite named class migrations', function() : void
{
    $migration = $this->parser->getMigration( realpath( 'tests/app/database/migrations/parser/foo_table.php' ), $this->uuid );

    $class = new ReflectionClass( get_class( $migration ) );

    expect( $class->isSubclassOf( Migration::class ) )->toBeTrue();
    expect( Str::of( $class->name )->explode( '\\' )->last() )->toBe( 'ThudTable' );
} );
