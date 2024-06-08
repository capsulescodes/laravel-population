<?php

use CapsulesCodes\Population\Tests\Cases\MariaDBTestCase;
use CapsulesCodes\Population\Tests\Cases\MySQLTestCase;
use CapsulesCodes\Population\Tests\Cases\SQLiteTestCase;
use CapsulesCodes\Population\Tests\Cases\TestCase;


uses( TestCase::class )->in( 'Unit/*.php', 'Feature/*.php' );
uses( SQLiteTestCase::class )->in( 'Unit/SQLite', 'Feature/SQLite' );
uses( MySQLTestCase::class )->in( 'Unit/MySQL', 'Feature/MySQL' );
uses( MariaDBTestCase::class )->in( 'Unit/MariaDB', 'Feature/MariaDB' );
