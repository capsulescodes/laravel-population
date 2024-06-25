<?php

namespace CapsulesCodes\Population\Tests\App\Database\Migrations\Parser;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class BarTable extends Migration
{
    public $name = 'foo';


    public function getName() : string
    {
        return 'bar';
    }

    public function up() : void
    {
        Schema::create( $this->name, function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'foo' );
            $table->timestamps();
        } );

        Schema::create( $this->getName(), function( Blueprint $table ) : void
        {
            $table->id();
            $table->boolean( 'bar' );
            $table->timestamps();
        } );
    }

    public function down() : void
    {
        Schema::dropIfExists( $this->name );

        Schema::dropIfExists( $this->getName() );
    }
}
