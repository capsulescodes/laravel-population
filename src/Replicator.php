<?php

namespace CapsulesCodes\Population;

use Exception;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class Replicator extends Migrator
{
    protected Parser $parser;
    protected Collection $tables;
    protected Collection $dirties;


    public function __construct( Migrator $migrator, Parser $parser )
    {
        parent::__construct( $migrator->repository, $migrator->resolver, $migrator->files, $migrator->events );

        $this->parser = $parser;
        $this->tables = Collection::make();
        $this->dirties = Collection::make();
    }

    public function getTables() : Collection
    {
        return $this->tables;
    }

    public function getDirties() : Collection
    {
        return $this->dirties;
    }

    public function replicate( string $database, string $uuid, array $files ) : void
    {
        if( ! $this->hasRunMigrations( $database ) ) throw new Exception( 'No tables migrated yet. Please run artisan migrate.' );

        $this->tables->splice( 0 );

        $this->dirties->splice( 0 );

        foreach( $files as $file )
        {
            $tables = $this->parser->getTables( $file );

            if( $tables->isEmpty() ) throw new Exception( 'No table names found in migration file. Please verify your migrations.' );

            $migration = $this->parser->getMigration( $file, $uuid );

            $connection = $migration->getConnection();

            if( $connection && $database != $connection ) continue;

            $this->runReplication( $file, $migration, $tables, 'up' );
        }
    }

    public function clean( string $uuid, Collection | null $deletables = null, bool $verbose = false ) : void
    {
        $deletables = $deletables ?? $this->tables;

        if( ! $deletables->count() ) return;

        if( $verbose ) $this->write( Info::class, "Rolling back '{$deletables->keys()->implode( ', ' )}' " . Str::plural( 'table replicate', $deletables->count() ) . '.' );

        foreach( $deletables->values() as $file )
        {
            $tables = $this->parser->getTables( $file );

            $migration = $this->parser->getMigration( $file, $uuid );

            $this->runReplication( $file, $migration, $tables, 'down' );
        }
    }

    protected function runReplication( string $file, Migration $migration, Collection $tables, string $method ) : void
    {
        $connection = $this->resolveConnection( $migration->getConnection() );

        $callback = function() use ( $connection, $file, $migration, $tables, $method )
        {
            if( method_exists( $migration, $method ) )
            {
                $tables->each( function( $table ) use ( $method, $file )
                {
                    if( $method === 'up' ) $this->tables = $this->tables->put( $table, $file );

                    if( $method === 'down' ) $this->tables->pull( $table );
                } );

                $this->runMethod( $connection, $migration, $method );
            }
        };

        $this->getSchemaGrammar( $connection )->supportsSchemaTransactions() && $migration->withinTransaction ? $connection->transaction( $callback ) : $callback();
    }

    protected function compare( string $database, string $uuid, Collection $tables ) : void
    {
        foreach( $tables->keys() as $table )
        {
            $oldTable = Collection::make( Schema::connection( $database )->getColumnListing( $table ) );
            $newTable = Collection::make( Schema::connection( $database )->getColumnListing( "{$table}-{$uuid}" ) );

            if( $oldTable->isNotEmpty() && $newTable->isNotEmpty() )
            {
                $changes = Collection::make();

                $columns = $oldTable->merge( $newTable )->unique();

                foreach( $columns as $column )
                {
                    try { $oldColumn = Schema::connection( $database )->getColumnType( $table, $column ); } catch ( Exception ) { $oldColumn = null; }
                    try { $newColumn = Schema::connection( $database )->getColumnType( "{$table}-{$uuid}", $column ); } catch ( Exception ) { $newColumn = null; }

                    if( $oldColumn !== $newColumn ) $changes->put( $column, [ 'old' => $oldColumn, 'new' => $newColumn ] );
                }

                if( $changes->isNotEmpty() ) $this->dirties->put( $table, $changes );
            }
        }
    }

    public function inspect( string $database, string $uuid ) : void
    {
        $this->compare( $database, $uuid, $this->tables );

        if( $this->dirties->isNotEmpty() )
        {
            $this->write( Info::class, 'Migration changes :' );

            foreach( $this->dirties->keys() as $key ) $this->write( Task::class, $this->getMigrationName( $this->tables[ $key ] ) );
        }
        else
        {
            $this->write( Info::class, 'No change in migrations' );
        }

        $this->clean( $uuid, $this->tables->filter( fn( $value, $key ) => ! $this->dirties->keys()->contains( $key ) ) );
    }

    public function databaseExists( string $connection ) : void
    {
        try { $this->resolveConnection( $connection )->getPdo(); } catch ( Exception ) { throw new Exception( 'Database not found. Please verify your credentials.' ); }
    }

    public function hasRunMigrations( string $database ) : bool
    {
        return ! Collection::make( Schema::connection( $database )->getTables() )->isEmpty();
    }
}
