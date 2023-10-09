<?php

namespace CapsulesCodes\Population;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;


class Dumper
{
    protected FileSystemAdapter $disk;

    protected string $filename;

    protected string $path;


    public function __construct()
    {
        $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

        $this->path = config( 'population.path' );
    }

    protected function makeDirectory() : void
    {
        if( ! $this->disk->exists( $this->path ) )
        {
            $this->disk->makeDirectory( $this->path );

            $this->disk->put( "{$this->path}/.gitignore", "*\n!.gitignore" );
        }
    }

    public function copy() : bool
    {
        $this->makeDirectory();

        $connection = config( 'database.connections.mysql' );

        $date = Carbon::now()->format('Y-m-d-H-i-s');

        $this->filename = "{$connection[ 'database' ]}-{$date}.sql";

        $command = "mysqldump --user={$connection[ 'username' ]} --password={$connection[ 'password' ]} --host={$connection[ 'host' ]} --order-by-primary {$connection[ 'database' ]} > {$this->disk->path( $this->path )}/{$this->filename}";

        $result = Process::run( $command );

        return $result->successful();
    }


    public function remove() : void
    {
        if( $this->disk->exists( "{$this->path}/{$this->filename}" ) ) $this->disk->delete( "{$this->path}/{$this->filename}" );
    }
}
