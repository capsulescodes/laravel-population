<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public function up() : void
    {
        Schema::create( 'baz', function( Blueprint $table )
        {
            $table->id();
            $table->boolean( 'baz' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( 'baz' );
    }
};
