<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function getConnection() : string
    {
        return 'quux';
    }

    public function up() : void
    {
        Schema::connection( 'corge' )->create( 'grault', function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'grault' );
            $table->timestamps();
        } );

        Schema::create( 'garply', function( Blueprint $table ) : void
         {
            $table->id();
            $table->boolean( 'garply' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'corge' )->dropIfExists( 'grault' );

        Schema::dropIfExists( 'garply' );
    }
};
