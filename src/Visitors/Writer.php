<?php

namespace CapsulesCodes\Population\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;


class Writer extends NodeVisitorAbstract
{
    private string $uuid;


    public function __construct( string $uuid )
    {
        $this->uuid = $uuid;
    }

    public function enterNode( Node $node )
    {
        if(
            $node instanceof Expression &&
            $node->expr instanceof MethodCall &&
            $node->expr->var instanceof StaticCall &&
            $node->expr->var->class instanceof Name &&
            $node->expr->var->class->name === 'Schema' &&
            $node->expr->name instanceof Identifier &&
            ( $node->expr->name->name == 'create' || $node->expr->name->name == 'dropIfExists' )
        )
        {
            $arg = $node->expr->args[ 0 ]->value;

            if( $arg instanceof String_ ) $arg->value = "{$arg->value}-{$this->uuid}";
        }

        if(
            $node instanceof Expression &&
            $node->expr instanceof StaticCall &&
            $node->expr->class instanceof Name &&
            $node->expr->class->name == 'Schema' &&
            $node->expr->name instanceof Identifier &&
            ( $node->expr->name->name == 'create' || $node->expr->name->name == 'dropIfExists' )
        )
        {
            $arg = $node->expr->args[ 0 ]->value;

            if( $arg instanceof String_ ) $arg->value = "{$arg->value}-{$this->uuid}";
        }
    }
}
