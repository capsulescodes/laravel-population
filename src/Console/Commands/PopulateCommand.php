<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use CapsulesCodes\Population\Dumper;


class PopulateCommand extends BaseCommand
{
    protected $signature = "populate";

    protected $description = "Manage your database using prompts";


    public function __construct( Dumper $dumper )
    {
        parent::__construct();

        $this->dumper = $dumper;
    }


    public function handle()
    {
        if( ! $this->dumper->copy() )
        {
            $this->error( "An error occurred when dumping your database" );

            // exit();

            return 1;
        }

        return 0;
    }
}
