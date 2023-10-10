<?php

namespace CapsulesCodes\Population;

use CapsulesCodes\Population\Traits\WriteTrait;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;


class Dumper
{
    use WriteTrait;


    private FileSystemAdapter $disk;
    private string $filename;
    private string $path;


    public function __construct()
    {
        $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

        $this->path = Config::get( 'population.path' );
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

    public function copy() : bool
    {
        if( ! $this->databaseExists() ) $this->write( Error::class, "An error occurred when dumping your database. Verify your credentials." );

        $this->makeDirectory();

        $connection = Config::get( 'database.connections.mysql' );

        $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

        $this->filename = "{$connection[ 'database' ]}-{$date}.sql";

        $command = "mysqldump --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} --order-by-primary {$connection[ 'database' ]} > {$this->disk->path( $this->path )}/{$this->filename}";

        $result = Process::run( $command );

        return $result->successful();
    }


    public function remove() : void
    {
        if( $this->disk->exists( "{$this->path}/{$this->filename}" ) ) $this->disk->delete( "{$this->path}/{$this->filename}" );

        if( Collection::make( $this->disk->files( $this->path ) )->count() == 1 && $this->disk->exists( "{$this->path}/.gitignore" ) ) $this->disk->deleteDirectory( $this->path );
    }
}
