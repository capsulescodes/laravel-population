<?php

namespace CapsulesCodes\Population\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;


class Reader extends NodeVisitorAbstract
{
    private string | null $namespace = null;
    private string | null $name = null;

    private array $nodes = [];
    private array $data = [];


    public function __construct()
    {
        $this->namespace = null;
        $this->name = null;
        $this->nodes = [];
        $this->data = [];
    }

    public function enterNode( Node $node )
    {
        if( $node instanceof Namespace_ )
        {
            $this->namespace = $node->name->name;

            $this->nodes[] = $node;
        }

        if(
            $node instanceof Class_ &&
            $node->name instanceof Identifier &&
            $node->extends instanceof Name &&
            $node->extends->name === 'Migration'
        )
        {
            $this->name = $node->name->name;
        }

        if(
            $node instanceof Use_ &&
            ! $this->namespace
        )
        {
            $this->nodes[] = $node;
        }

        if(
            $node instanceof Return_ &&
            $node->expr instanceof New_ &&
            $node->expr->class instanceof Class_ &&
            $node->expr->class->extends instanceof Name &&
            $node->expr->class->extends->name === 'Migration' &&
            ! $this->namespace
        )
        {
            $this->nodes[] = $node;
        }

        if(
            $node instanceof Expression &&
            $node->expr instanceof MethodCall &&
            $node->expr->var instanceof StaticCall &&
            $node->expr->var->class instanceof Name &&
            $node->expr->var->class->name === 'Schema' &&
            $node->expr->name instanceof Identifier &&
            $node->expr->name->name === 'create'
        )
        {
            $arg = $node->expr->args[ 0 ]->value;

            if( $arg instanceof String_ ) $this->data[] = $arg->value;
        }

        if(
            $node instanceof Expression &&
            $node->expr instanceof StaticCall &&
            $node->expr->class instanceof Name &&
            $node->expr->class->name == 'Schema' &&
            $node->expr->name instanceof Identifier &&
            $node->expr->name->name == 'create'
        )
        {
            $arg = $node->expr->args[ 0 ]->value;

            if( $arg instanceof String_ ) $this->data[] = $arg->value;
        }
    }

    public function getName() : string | null
    {
        return $this->namespace && $this->name ? "{$this->namespace}\\{$this->name}" : null;
    }

    public function getNodes() : array
    {
        return $this->nodes;
    }

    public function getData() : array
    {
        return $this->data;
    }
}
