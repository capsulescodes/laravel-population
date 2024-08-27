<?php

use CapsulesCodes\Population\Tests\Cases\TestCase;
use CapsulesCodes\Population\Tests\Cases\SQLiteTestCase;
use CapsulesCodes\Population\Tests\Cases\MySQLTestCase;
use CapsulesCodes\Population\Tests\Cases\MariaDBTestCase;
use CapsulesCodes\Population\Tests\Cases\PostgreSQLTestCase;


uses( TestCase::class )->group( 'continuous-integration' )->in( 'Unit/*.php', 'Feature/*.php' );
uses( SQLiteTestCase::class )->group( 'continuous-integration' )->in( 'Unit/SQLite', 'Feature/SQLite' );
uses( MySQLTestCase::class )->in( 'Unit/MySQL', 'Feature/MySQL' );
uses( MariaDBTestCase::class )->in( 'Unit/MariaDB', 'Feature/MariaDB' );
uses( PostgreSQLTestCase::class )->in( 'Unit/PostgreSQL', 'Feature/PostgreSQL' );
