<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 24/01/2021 22:46
 */

$rootDir = dirname(__DIR__);

// Include the composer autoloader
$loader = require $rootDir . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::create($rootDir, '.env.testing');
$dotenv->load();
$dotenv->required(['DASHAMAIL_USERNAME', 'DASHAMAIL_PASSWORD']);
