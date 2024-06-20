<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::connection( 'two' )->create( 'grault', function( Blueprint $table ) : void
        {
            $table->id();
            $table->string( 'grault' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'two' )->dropIfExists( 'grault' );
    }
};
