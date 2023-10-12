<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public $table = 'qux';

    public function up() : void
    {
        Schema::create( $this->table, function( Blueprint $table )
        {
            $table->id();
            $table->boolean( 'qux' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( $this->table );
    }
};
