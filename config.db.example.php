<?php

$db_source = 'database_source';
$db_destination = 'database_app';
$db_host = '127.0.0.1';
$db_user = '';
$db_pass = '';
$brand = 'brand';

$connections = (object)[
    $db_source => [$db_host,$db_user,$db_pass],
    $db_destination => [$db_host,$db_user,$db_pass]
];