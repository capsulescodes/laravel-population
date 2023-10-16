<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public $name = 'qux';

    public function up() : void
    {
        Schema::create( $this->name, function( Blueprint $table )
        {
            $table->id();
            $table->boolean( 'qux' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( $this->name );
    }
};
