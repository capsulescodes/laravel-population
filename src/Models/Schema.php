<?php

namespace CapsulesCodes\Population\Models;

use Illuminate\Support\Str;


class Schema
{
    public string | null $connection;
    public string $table;
    public string $code;
    public string $file;


    public function __construct( string | null $connection, string $table, string $file )
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->code = Str::random( Str::length( $this->table ) );
        $this->file = $file;
    }

    public function getName()
    {
        return ( $this->connection ?? 'default' ) . ".$this->table";
    }
}
