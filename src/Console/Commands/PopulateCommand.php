<?php

namespace CapsulesCodes\Population\Console\Commands;

use Illuminate\Console\Command;


class PopulateCommand extends Command
{
    protected $signature = "populate";

    protected $description = "Manage your database using prompts";

    public function handle()
    {
        $this->info( "Hello World" );
    }
}
