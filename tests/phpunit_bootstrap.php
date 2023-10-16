<?php

$loader = require __DIR__.'/../vendor/autoload.php';

setlocale(LC_TIME, 'ru_RU.UTF-8');

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

$dotenv = new Dotenv\Dotenv(ROOT);
$dotenv->load();
