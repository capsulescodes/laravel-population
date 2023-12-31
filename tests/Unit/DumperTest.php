<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use CapsulesCodes\Population\Dumper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


beforeEach( function()
{
    $this->dumper = new Dumper();

    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

    $this->path = Config::get( 'population.path' );

    $this->database = Config::get( 'database.connections.mysql.database' );
});

afterEach( function()
{
    $this->disk->deleteDirectory( $this->path );
});




it( "returns false if current database doesn't exist", function()
{
    Config::set( 'database.connections.mysql.database', 'no-package' );

    $dumper = new Dumper();

    expect( fn() => $dumper->copy() )->toThrow( Exception::class );

    Config::set( 'database.connections.mysql.database', $this->database );
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

    Config::set( 'population.path', $path );

    $dumper = new Dumper();

    $dumper->copy();

    expect( $this->disk->exists( $path ) )->toBeTrue();

    $this->disk->deleteDirectory( $path );

    Config::set( 'population.path', $this->path );
});


it( "makes a dump of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
});


it( "makes multiple dumps of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    Carbon::setTestNow( $date->addMinute() );

    $this->dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
});


it( "removes the latest dump of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    Carbon::setTestNow( $date->addMinute() );

    $this->dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    $this->dumper->remove();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->not()->toContain( "{$this->path}/{$this->database}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
});


it( "removes the existing directory if no database files", function()
{
    $this->dumper->copy();

    $this->dumper->remove();

    expect( $this->disk->exists( $this->path ) )->not()->toBeTrue();
});


it( "rolls back the latest database dump", function()
{
    $this->dumper->copy();

    expect( fn() => $this->dumper->revert() )->not()->toThrow( Exception::class );
});
