<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::create( 'foo', function( Blueprint $table ) : void
        {
            $table->id();
            $table->string( 'foo' );
            $table->boolean( 'baz' );
            $table->integer( 'qux' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::dropIfExists( 'foo' );
    }
};
