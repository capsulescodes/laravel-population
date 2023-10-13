<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public function up() : void
    {
        Schema::create( 'qux', function( Blueprint $table )
        {
            $table->id();
            $table->boolean( 'qux' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( 'qux' );
    }
};
