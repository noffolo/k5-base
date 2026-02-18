<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/kirby/bootstrap.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

echo (new Kirby)->render();
