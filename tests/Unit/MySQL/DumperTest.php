<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use CapsulesCodes\Population\Dumper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


beforeEach( function()
{
    $this->database = Config::get( 'database.default' );

    $this->filename = Str::of( basename( Config::get( "database.connections.{$this->database}.database" ) ) )->explode( '.' )->first();

    $this->dumper = new Dumper();

    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

    $this->path = Config::get( 'population.path' );
} );

afterEach( function()
{
    $this->disk->deleteDirectory( $this->path );
} );




it( "creates a dump directory", function()
{
    $dumper = new Dumper();

    $dumper->copy( $this->database );

    expect( $this->disk->exists( $this->path ) )->toBeTrue();
} );


it( "creates a dump directory with a given path", function()
{
    $path = "app/databases";

    Config::set( 'population.path', $path );

    $dumper = new Dumper();

    $dumper->copy( $this->database );

    expect( $this->disk->exists( $path ) )->toBeTrue();

    $this->disk->deleteDirectory( $path );

    Config::set( 'population.path', $this->path );
} );


it( "makes a dump of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy( $this->database );

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
} );


it( "makes multiple dumps of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy( $this->database );

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    Carbon::setTestNow( $date->addMinute() );

    $this->dumper->copy( $this->database );

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
} );


it( "removes the latest dump of the current database", function()
{
    $date = Carbon::now();

    Carbon::setTestNow( $date );

    $this->dumper->copy( $this->database );

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    Carbon::setTestNow( $date->addMinute() );

    $this->dumper->copy( $this->database );

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );

    $this->dumper->remove();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->not()->toContain( "{$this->path}/{$this->filename}-{$date->format( 'Y-m-d-H-i-s' )}.sql" );
} );


it( "removes the existing directory if no database files", function()
{
    $this->dumper->copy( $this->database );

    $this->dumper->remove();

    expect( $this->disk->exists( $this->path ) )->not()->toBeTrue();
} );


it( "rolls back the latest database dump", function()
{
    $this->dumper->copy( $this->database );

    expect( fn() => $this->dumper->revert() )->not()->toThrow( Exception::class );
} );
