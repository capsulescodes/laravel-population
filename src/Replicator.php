<?php

namespace CapsulesCodes\Population;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Exception;


class Replicator extends Migrator
{
    protected Collection $tables;
    protected Collection $dirties;


    public function __construct( Migrator $migrator )
    {
        parent::__construct( $migrator->repository, $migrator->resolver, $migrator->files, $migrator->events );

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
        if( ! $this->hasRunMigrations( $database ) ) throw new Exception( "No tables migrated yet, please run artisan migrate." );

        $this->tables->splice( 0 );
        $this->dirties->splice( 0 );

        $this->requireFiles( $files );

        foreach( $files as $file )
        {
            $migration = $this->resolvePath( $file );

            $connection = $migration->getConnection();

            if( $connection && $database != $connection ) continue;

            $this->runReplication( $uuid, $file, $migration, 'up' );
        }
    }

    public function clean( string $uuid, Collection | null $deletables = null, bool $verbose = false ) : void
    {
        $deletables = $deletables ?? $this->tables;

        if( ! $deletables->count() ) return;

        if( $verbose ) $this->write( Info::class, "Rolling back '{$deletables->keys()->implode( ', ' )}' " . Str::plural( 'table replicate', $deletables->count() ) . "." );

        foreach( $deletables->values() as $file )
        {
            $migration = $this->resolvePath( $file );

            $this->runReplication( $uuid, $file, $migration, 'down' );
        }
    }

    protected function runReplication( string $uuid, string $file, object $migration, string $method ) : void
    {
        $connection = $this->resolveConnection( $migration->getConnection() );

        $callback = function () use ( $connection, $uuid, $file,  $migration, $method )
        {
            if( property_exists( $migration, 'name' ) && method_exists( $migration, $method ) )
            {
                if( $method === 'up' ) $this->tables->put( $migration->name, $file );

                if( $method === 'down' ) $this->tables->pull( $migration->name );

                $migration->name = "{$migration->name}-{$uuid}";

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
                    try { $oldColumn = Schema::connection( $database )->getColumnType( $table, $column ); } catch( Exception ) { $oldColumn = null; }

                    try { $newColumn = Schema::connection( $database )->getColumnType( "{$table}-{$uuid}", $column ); } catch( Exception ) { $newColumn = null; }

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
            $this->write( Info::class, "Migration changes :" );

            foreach( $this->dirties->keys() as $key )
            {
                $this->write( Task::class, $this->getMigrationName( $this->tables[ $key ] ) );
            }
        }
        else
        {
            $this->write( Info::class, "No change in migrations" );
        }

        $this->clean( $uuid, $this->tables->filter( fn( $value, $key ) => ! $this->dirties->keys()->contains( $key ) ) );
    }

    public function databaseExists( string $connection ) : void
    {
        try
        {
           $this->resolveConnection( $connection )->getPdo();
        }
        catch( Exception )
        {
            throw new Exception( "Database not found. Verify your credentials." );
        }
    }

    public function hasRunMigrations( string $database ) : bool
    {
       return ! Collection::make( Schema::connection( $database )->getTables() )->isEmpty();
    }
}
