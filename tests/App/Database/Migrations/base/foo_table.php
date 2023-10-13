<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public function up() : void
    {
        Schema::create( 'foo', function( Blueprint $table )
        {
            $table->id();
            $table->string( 'foo' );
            $table->boolean( 'baz' );
            $table->integer( 'qux' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( 'foo' );
    }
};
