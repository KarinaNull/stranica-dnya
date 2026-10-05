<?php

$db_name = 'stranica_dnya';

$config = [
    'host' => 'localhost',
    'port' => '5432',
    'user' => 'postgres',
    'password' => '',
];

if (file_exists(__DIR__ . '/config.php')) {
    $config = require __DIR__ . '/config.php';
}

function connect_string($config, $db_name)
{
    $password = addcslashes($config['password'], "'\\");
    return "host={$config['host']} port={$config['port']} dbname=$db_name user={$config['user']} password='$password' options='--client_encoding=UTF8'";
}
