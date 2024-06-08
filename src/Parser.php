<?php

namespace CapsulesCodes\Population;

use CapsulesCodes\Population\Visitors\Reader;
use CapsulesCodes\Population\Visitors\Writer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;


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

    public function getTables( string $file ) : Collection
    {
        $code = File::get( $file );

        $ast = $this->parser->parse( $code );

        $visitor = new Reader();

        $this->traverse( $ast, $visitor );

        return Collection::make( $visitor->getData() );
    }

    public function getMigration( string $file, string $uuid ) : Migration
    {
        $code = File::get( $file );

        $ast = $this->parser->parse( $code );

        $visitor = new Reader();

        $this->traverse( $ast, $visitor );

        $ast = $this->traverse( $ast, new Writer( $uuid ) );

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
