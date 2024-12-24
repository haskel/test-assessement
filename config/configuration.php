<?php

return [
    'db' => [
        'dbname' => 'app',
        'user' => 'app',
        'password' => 'app',
        'host' => 'database',
        'driver' => 'pdo_mysql',
    ],
    'forbiddenDomains' => [
        'forbidden.org' => true
    ],
    'restrictedWords' => [
        'admin',
        'root',
        'superuser'
    ]
];
