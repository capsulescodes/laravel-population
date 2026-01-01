<?php

namespace CapsulesCodes\Population;

use CapsulesCodes\Population\Parser;
use Illuminate\Support\Collection;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Illuminate\Console\View\Components\Task;
use Illuminate\Support\Facades\Schema;
use Exception;


class Replicator extends Migrator
{
    protected Parser $parser;
    protected Collection $schemas;
    protected Collection $dirties;


    public function __construct( Migrator $migrator, Parser $parser )
    {
        parent::__construct( $migrator->repository, $migrator->resolver, $migrator->files, $migrator->events );

        $this->parser = $parser;
        $this->schemas = Collection::make();
        $this->dirties = Collection::make();
    }

    public function getSchemas() : Collection
    {
        return $this->schemas;
    }

    public function getDirties() : Collection
    {
        return $this->dirties;
    }

    public function replicate( array $files ) : void
    {
        if( ! $this->hasRunMigrations() ) throw new Exception( 'No tables migrated yet. Please run artisan migrate.' );

        $this->schemas->splice( 0 );

        $this->dirties->splice( 0 );

        foreach( $files as $file )
        {
            $schemas = $this->parser->getSchemas( $file );

            if( $schemas->isEmpty() ) throw new Exception( "No table name found in migration file '{$file}'. Please verify your migrations." );

            $schemas = $schemas->filter( fn( $schema ) => $this->resolveConnection( $schema->connection )->getName() === $this->getConnection() );

            if( $schemas->isEmpty() ) continue;

            $migration = $this->parser->resolveMigration( $file, $schemas );

            $this->runReplication( $migration, $schemas, 'up' );
        }
    }

    public function clean( Collection | null $deletables = null, bool $verbose = false ) : void
    {
        $deletables = $deletables ?? $this->schemas;

        if( ! $deletables->count() ) return;

        if( $verbose ) $this->write( Info::class, "Rolling back '{$deletables->values()->pluck( 'table' )->implode( ', ' )}' " . Str::plural( 'table replicate', $deletables->count() ) . '.' );

        foreach( $deletables as $deletable )
        {
            $connection = $this->resolveConnection( $deletable->connection );

            $connection->getSchemaBuilder()->dropIfExists( $deletable->code );

            $this->schemas->pull( $deletable->getName() );
        }
    }

    protected function runReplication( Migration $migration, Collection $schemas ) : void
    {
        $connection = $this->resolveConnection( $migration->getConnection() );

        $callback = function() use ( $connection, $migration, $schemas )
        {
            if( method_exists( $migration, "up" ) )
            {
                $schemas->each( fn( $schema ) => $this->schemas = $this->schemas->put( $schema->getName(), $schema ) );

                $this->runMethod( $connection, $migration, "up" );
            }
        };

        $this->getSchemaGrammar( $connection )->supportsSchemaTransactions() && $migration->withinTransaction ? $connection->transaction( $callback ) : $callback();
    }

    protected function compare() : void
    {
        foreach( $this->schemas as $schema )
        {
            $oldTable = Collection::make( Schema::connection( $this->getConnection() )->getColumnListing( $schema->table ) );
            $newTable = Collection::make( Schema::connection( $this->getConnection() )->getColumnListing( $schema->code ) );

            if( $oldTable->isNotEmpty() && $newTable->isNotEmpty() )
            {
                $changes = Collection::make();

                $columns = $oldTable->merge( $newTable )->unique();

                foreach( $columns as $column )
                {
                    try { $oldColumn = Schema::connection( $this->getConnection() )->getColumnType( $schema->table, $column ); } catch ( Exception ) { $oldColumn = null; }
                    try { $newColumn = Schema::connection( $this->getConnection() )->getColumnType( $schema->code, $column ); } catch ( Exception ) { $newColumn = null; }

                    if( $oldColumn !== $newColumn ) $changes->put( $column, [ 'old' => $oldColumn, 'new' => $newColumn ] );
                }

                if( $changes->isNotEmpty() ) $this->dirties->put( $schema->getName(), $changes );
            }
        }
    }

    public function inspect() : void
    {
        $this->compare();

        if( $this->dirties->isNotEmpty() )
        {
            $this->write( Info::class, 'Migration changes :' );

            foreach( $this->dirties->keys() as $key ) $this->write( Task::class, $this->getMigrationName( $this->schemas->get( $key )->file ) );
        }
        else
        {
            $this->write( Info::class, 'No change in migrations' );
        }

        $this->clean( $this->schemas->filter( fn( $value, $key ) => ! $this->dirties->keys()->contains( $key ) ) );
    }

    public function hasRunMigrations() : bool
    {
        $connection = Schema::connection( $this->getConnection() );

        return Collection::make( $connection->getTables( $connection->getCurrentSchemaName() ) )->isNotEmpty();
    }
}
