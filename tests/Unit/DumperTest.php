<?php

use CapsulesCodes\Population\Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use CapsulesCodes\Population\Dumper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


uses( TestCase::class );

beforeEach( function()
{
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
    Config::set( 'database.connections.mysql.database', 'laravel' );

    $dumper = new Dumper();

    expect( $dumper->copy() )->toBeFalse();

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
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});


it( "makes multiple dumps of the current database", function()
{
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );

    Carbon::setTestNow( Carbon::now()->addMinute() );

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});


it( "removes the latest dump of the current database", function()
{
    $dumper = new Dumper();

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );

    Carbon::setTestNow( Carbon::now()->addMinute() );

    $date = Carbon::now()->format( 'Y-m-d-H-i-s' );

    $dumper->copy();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->toContain( "{$this->path}/{$this->database}-{$date}.sql" );

    $dumper->remove();

    expect( Collection::make( $this->disk->files( $this->path ) ) )->not()->toContain( "{$this->path}/{$this->database}-{$date}.sql" );
});

it( "removes the existing directory if no database files", function()
{
    $dumper = new Dumper();

    $dumper->copy();

    $dumper->remove();

    expect( $this->disk->exists( $this->path ) )->not()->toBeTrue();
});
