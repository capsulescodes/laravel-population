<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::connection( 'two' )->create( 'quux', function( Blueprint $table ) : void
        {
            $table->id();
            $table->string( 'quux' );
            $table->string( 'grault' );
            $table->string( 'waldo' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'two' )->dropIfExists( 'quux' );
    }
};
