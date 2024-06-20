<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::connection( 'two' )->create( 'waldo', function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'waldo' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'two' )->dropIfExists( 'waldo' );
    }
};
