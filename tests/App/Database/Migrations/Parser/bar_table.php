<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::create( 'bar', function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'bar' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::dropIfExists( 'bar' );
    }
};
