<?php

namespace CapsulesCodes\Population;

use Illuminate\Database\Migrations\Migrator;


class Purgator extends Migrator
{
    public function __construct( Migrator $migrator )
    {
        parent::__construct( $migrator->repository, $migrator->resolver, $migrator->files, $migrator->events );
    }

    public function purge( $files ) : void
    {
        if( ! $this->hasRunAnyMigrations() ) throw new Exception( "No tables migrated yet, please run artisan migrate." );

        $this->requireFiles( $files );

        foreach( $files as $file )
        {
            $migration = $this->resolvePath( $file );

            $this->runPurgation( $migration, 'down' );
        }
    }

    protected function runPurgation( $migration, $method ) : void
    {
        $connection = $this->resolveConnection( $migration->getConnection() );

        $callback = function () use ( $connection, $migration, $method )
        {
            if( method_exists( $migration, $method ) )
            {
                $this->runMethod( $connection, $migration, $method );
            }
        };

        $this->getSchemaGrammar( $connection )->supportsSchemaTransactions() && $migration->withinTransaction ? $connection->transaction( $callback ) : $callback();
    }
}
