<?php
declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as MySQLDriver;

if (!is_file(dirname(__DIR__).'/vendor/autoload.php')) {
    throw new LogicException('Run "composer install" to install dependencies');
}

require_once dirname(__DIR__).'/vendor/autoload.php';

$config = require dirname(__DIR__).'/config/configuration.php';
$connection = new Connection($config['db'], new MySQLDriver());

$createUserTable = <<<SQL
create table users
(
    id      int          auto_increment,
    name    varchar(64)  not null,
    email   varchar(256) not null,
    created datetime     not null,
    deleted datetime     null,
    notes   text         null,
    
    constraint users_pk primary key (id)
);

create unique index users_email_uindex on users (email);

create unique index users_name_uindex on users (name);
SQL;


$tables = $connection->fetchAssociative('show tables like "users"');
if ($tables) {
    exit(0);
}

$connection->executeStatement($createUserTable);


