<?php

namespace CapsulesCodes\Population\Models;


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
        $this->code = "_" . $this->table . "_" . now()->format( 'YmdHis' );
        $this->file = $file;
    }

    public function getName()
    {
        return ( $this->connection ?? 'default' ) . ".$this->table";
    }
}
