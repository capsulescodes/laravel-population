<?php

namespace CapsulesCodes\Population;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Collection;
use Exception;


class Dumper
{
    private FileSystemAdapter $disk;

    private string $path;

    private string $filename;


    public function __construct()
    {
        $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

        $this->path = Config::get( 'population.path' );

        $this->filename = "";
    }

    protected function makeDirectory() : void
    {
        if( ! $this->disk->exists( $this->path ) )
        {
            $this->disk->makeDirectory( $this->path );

            $this->disk->put( "{$this->path}/.gitignore", "*\n!.gitignore" );
        }
    }

    protected function databaseExists() : bool
    {
        $connection = Config::get( 'database.connections.mysql' );

        $command = "mysql --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} {$connection[ 'database' ]}";

        $result = Process::run( $command );

        return $result->successful();
    }

    public function copy() : void
    {
        if( ! $this->databaseExists() ) throw new Exception( "An error occurred when dumping your database. Verify your credentials." );

        $this->makeDirectory();

        $connection = Config::get( 'database.connections.mysql' );

        $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

        $this->filename = "{$connection[ 'database' ]}-{$date}.sql";

        $command = "mysqldump --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} --order-by-primary {$connection[ 'database' ]} > {$this->disk->path( $this->path )}/{$this->filename}";

        $result = Process::run( $command );

        if( $result->failed() ) throw new Exception( "An error occurred when dumping your database. Verify your credentials." );
    }


    public function remove() : void
    {
        if( $this->disk->exists( "{$this->path}/{$this->filename}" ) ) $this->disk->delete( "{$this->path}/{$this->filename}" );

        if( Collection::make( $this->disk->files( $this->path ) )->count() == 1 && $this->disk->exists( "{$this->path}/.gitignore" ) ) $this->disk->deleteDirectory( $this->path );
    }
}
