<?php

namespace CapsulesCodes\Population\Tests\App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pest\TestSuite;


trait Configurable
{
    private static int $count = 0;
    private static Collection $tests;


    protected function setUp() : void
    {
        parent::setUp();

        if( ! self::$count )
        {
            $this->init();

            if( method_exists( self::class, 'initialize' ) ) $this->initialize();
        }

        self::$count++;
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        if( count( self::$tests ) == self::$count )
        {
            if( method_exists( self::class, 'finalize' ) ) $this->finalize();
        }
    }

    private function init() : void
    {
        $repository = TestSuite::getInstance()->tests;

        $data = [];

        foreach( $repository->getFilenames() as $file )
        {
            $factory = $repository->get( $file );

            $filename = Str::of( $file )->basename()->explode( '.' )->first();

            if( $factory->class === self::class ) $data = [ ...$data, ...[ $filename => $factory->methods ] ];
        }

        $cases = Collection::make( Arr::dot( $data ) );

        $only = $cases->filter( fn( $case ) => Collection::make( $case->groups )->contains( '__pest_only' ) );

        self::$tests = ( $only->isEmpty() ? $cases : $only )->keys()->map( fn( $key ) => Str::of( $key )->kebab );
    }
}
