<?php

namespace CapsulesCodes\Population\Tests;

use CapsulesCodes\Population\Tests\TestCase as BaseTestCase;


abstract class DatabaseTestCase extends BaseTestCase
{
    protected function defineDatabaseMigrations() : void
    {
        $this->loadMigrationsFrom( 'tests/database/migrations/base' );
    }
}
