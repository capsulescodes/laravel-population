<?php

namespace CapsulesCodes\Population;

use PhpParser\Parser as PhpParser;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\ParserFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use CapsulesCodes\Population\Visitors\Reader;
use CapsulesCodes\Population\Models\Schema;
use CapsulesCodes\Population\Visitors\Writer;
use Illuminate\Database\Migrations\Migration;
use PhpParser\NodeVisitor;


class Parser
{
    protected PhpParser $parser;
    protected NodeTraverser $traverser;
    protected Standard $printer;


    public function __construct( ParserFactory $factory, NodeTraverser $traverser, Standard $printer )
    {
        $this->parser = $factory->createForNewestSupportedVersion();
        $this->traverser = $traverser;
        $this->printer = $printer;
    }

    public function getSchemas( string $file ) : Collection
    {
        $code = File::get( $file );

        $ast = $this->parser->parse( $code );

        $visitor = new Reader();

        $this->traverse( $ast, $visitor );

        return Collection::make( $visitor->getData() )->map( fn( $data ) => new Schema( $data[ 'connection' ], $data[ 'table' ] , $file ) );
    }

    public function getMigration( string $file, Collection $schemas ) : array
    {
        $code = File::get( $file );

        $ast = $this->parser->parse( $code );

        return $this->traverse( $ast, new Writer( $schemas ) );
    }

    public function resolveMigration( string $file, Collection $schemas ) : Migration
    {
        $ast = $this->getMigration( $file, $schemas );

        $visitor = new Reader();

        $this->traverse( $ast, $visitor );

        if( $class = $visitor->getName() )
        {
            if( ! class_exists( $class ) )
            {
                eval( $this->printer->prettyPrint( $ast ) );
            }

            return new $class();
        }

        return eval( $this->printer->prettyPrint( $ast ) );
    }

    public function traverse( array $ast, NodeVisitor $visitor ) : array
    {
        $this->traverser->addVisitor( $visitor );

        $ast = $this->traverser->traverse( $ast );

        $this->traverser->removeVisitor( $visitor );

        return $ast;
    }
}
