<?php

namespace CapsulesCodes\Population\Visitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;


class Reader extends NodeVisitorAbstract
{
    private string | null $namespace = null;
    private string | null $name = null;
    private string | null $connection = null;

    private array $data = [];


    public function enterNode( Node $node )
    {
        if( $node instanceof Namespace_ )
        {
            $this->namespace = $node->name->name;
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
            $node instanceof ClassMethod &&
            $node->name instanceof Identifier &&
            $node->name->name === 'getConnection'
        )
        {
            $connection = $node->stmts[ 0 ]->expr;

            if( $connection instanceof String_ ) $this->connection = $connection->value;
        }

        if(
            $node instanceof Expression &&
            $node->expr instanceof MethodCall &&
            $node->expr->var instanceof StaticCall &&
            $node->expr->var->class instanceof Name &&
            $node->expr->var->class->name === 'Schema' &&
            $node->expr->var->name instanceof Identifier &&
            $node->expr->var->name->name === 'connection' &&
            $node->expr->name instanceof Identifier &&
            $node->expr->name->name === 'create'
        )
        {
            $connection = $node->expr->var->args[ 0 ]->value;
            $table = $node->expr->args[ 0 ]->value;

            if( $connection instanceof String_ && $table instanceof String_ ) $this->data[] = [ 'connection' => $connection->value, 'table' => $table->value ]; return;
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
            $table = $node->expr->args[ 0 ]->value;

            if( $table instanceof String_ ) $this->data[] = [ 'connection' => $this->connection, 'table' => $table->value ]; return;
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
            $table = $node->expr->args[ 0 ]->value;

            if( $table instanceof String_ ) $this->data[] = [ 'connection' => $this->connection, 'table' => $table->value ]; return;
        }
    }

    public function getName() : string | null
    {
        return $this->namespace && $this->name ? "{$this->namespace}\\{$this->name}" : null;
    }

    public function getData() : array
    {
        return $this->data;
    }
}
