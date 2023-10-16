<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public $name = 'foo';

    public function up() : void
    {
        Schema::create( $this->name, function( Blueprint $table )
        {
            $table->id();
            $table->string( 'foo' );
            $table->string( 'bar' );
            $table->string( 'qux' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( $this->name );
    }
};
