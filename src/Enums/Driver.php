<?php

namespace CapsulesCodes\Population\Enums;

enum Driver : string
{
    case SQLite = 'sqlite';
    case MySQL = 'mysql';
    case MariaDB = 'mariadb';
}
