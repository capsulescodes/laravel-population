<?php

namespace CapsulesCodes\Population;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use CapsulesCodes\Population\Enums\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Collection;
use Exception;


class Dumper
{
    protected FileSystemAdapter $disk;

    protected string $path;

    protected string $filename;


    public function __construct()
    {
        $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

        $this->path = Config::get( 'population.path', Storage::path( '/' ) );

        $this->filename = '';
    }

    protected function makeDirectory() : void
    {
        if( ! $this->disk->exists( $this->path ) )
        {
            $this->disk->makeDirectory( $this->path );

            $this->disk->put( "{$this->path}/.gitignore", "*\n!.gitignore" );
        }
    }

    public function copy( string $name ) : void
    {
        $this->makeDirectory();

        $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

        $connection = Config::get( "database.connections.{$name}" );

        $driver = Driver::from( $connection[ 'driver' ] );

        if( $driver == Driver::SQLite )
        {
            $chunk = Str::of( $connection[ 'database' ] )->basename()->explode( '.' )->first();

            $this->filename = Str::of( $chunk )->append( "-{$date}.sqlite" );

            $command = "cp {$connection[ 'database' ]} {$this->disk->path( $this->path )}/{$this->filename}";

            $result = Process::run( $command );

            if( $result->failed() ) throw new Exception( "An error occurred while dumping your database. Verify your credentials." );
        }
        else if( $driver == Driver::MySQL || $driver == Driver::MariaDB )
        {
            $this->filename = "{$connection[ 'database' ]}-{$date}.sql";

            $command = "mysqldump --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} --order-by-primary {$connection[ 'database' ]} > {$this->disk->path( $this->path )}/{$this->filename}";

            $result = Process::run( $command );

            if( $result->failed() ) throw new Exception( "An error occurred while dumping your database. Verify your credentials." );
        }
        else
        {
           throw new Exception( "An error occurred while dumping your database. Connection driver not supported." );
        }
    }

    public function revert( string $name ) : void
    {
        $files = Collection::make( $this->disk->exists( $this->path ) ? $this->disk->allFiles( $this->path ) : [] );

        $connection = Config::get( "database.connections.{$name}" );

        $driver = Driver::from( $connection[ 'driver' ] );

        $chunk = Str::of( $connection[ 'database' ] )->basename()->explode( '.' )->first();

        $dumps = $files->filter( fn( $file ) => Str::of( $file )->contains( $chunk ) );

        if( $dumps->isEmpty() ) throw new Exception( "No database dump left in directory." );

        if( $driver == Driver::SQLite )
        {
            $command = "mv {$this->disk->path( $dumps->last() )} {$connection[ 'database' ]}";

            $result = Process::run( $command );

            if( $result->failed() ) throw new Exception( "An error occurred while setting back your database. Verify your credentials." );
        }
        else if( $driver == Driver::MySQL || $driver == Driver::MariaDB )
        {
            $command = "mysql --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} {$connection[ 'database' ]} < {$this->disk->path( $dumps->last() )}";

            $result = Process::run( $command );

            if( $result->failed() ) throw new Exception( "An error occurred while setting back your database. Verify your credentials." );
        }
        else
        {
            throw new Exception( "An error occurred while dumping your database. Connection driver not supported." );
        }
    }

    public function remove() : void
    {
        if( $this->disk->exists( "{$this->path}/{$this->filename}" ) ) $this->disk->delete( "{$this->path}/{$this->filename}" );

        if( Collection::make( $this->disk->files( $this->path ) )->count() == 1 && $this->disk->exists( "{$this->path}/.gitignore" ) ) $this->disk->deleteDirectory( $this->path );
    }
}
