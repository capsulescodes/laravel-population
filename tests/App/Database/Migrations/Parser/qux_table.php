<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up() : void
    {
        Schema::connection( 'qux' )->create( 'quux', function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'quux' );
            $table->timestamps();
        } );

        Schema::create( 'corge', function( Blueprint $table ) : void
         {
            $table->id();
            $table->boolean( 'corge' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::connection( 'qux' )->dropIfExists( 'quux' );

        Schema::dropIfExists( 'corge' );
    }
};
