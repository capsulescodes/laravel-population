<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::connection( 'one' )->create( 'foo', function( Blueprint $table ) : void
        {
            $table->id();
            $table->string( 'foo' );
            $table->string( 'bar' );
            $table->string( 'qux' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'one' )->dropIfExists( 'foo' );
    }
};
