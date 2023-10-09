<?php

use Illuminate\Support\Facades\Storage;
use CapsulesCodes\Population\Dumper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;


beforeEach( function()
{
    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

    $this->path = config( 'population.path' );

    $this->database = config( 'database.connections.mysql' )[ 'database' ];
});

afterEach( function()
{
    $this->disk->deleteDirectory( $this->path );
});




it( "creates a dump directory", function()
{
    $dumper = new Dumper();

    $dumper->copy();

    expect( $this->disk->exists( $this->path ) )->toBeTrue();
});


it( "creates a dump directory with a given path", function()
{
    $path = "app/databases";

    Env::getRepository()->set( 'DB_DUMP_PATH', $path );

    $this->refreshApplication();

    $dumper = new Dumper();

    $dumper->copy();

    expect( $this->disk->exists( $path ) )->toBeTrue();

    $this->disk->deleteDirectory( $path );

    Env::getRepository()->clear( 'DB_DUMP_PATH' );

    $this->refreshApplication();
});


it( "makes a dump of the current database", function()
{
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( collect( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});


it( "makes multiple dumps of the current database", function()
{
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( collect( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );

    Carbon::setTestNow( Carbon::now()->addMinute() );

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( collect( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});


it( "removes the latest dump of the current database", function()
{
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( collect( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );

    $dumper->remove();

    expect( collect( $this->disk->files( $this->path ) ) )->not()->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});


it( "returns an error if current database doesn't exist", function()
{
    $dumper = new Dumper();

    expect( $dumper->copy() )->toBeFalse();
});
