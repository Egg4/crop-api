<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

define('ROOT_DIR', dirname(__DIR__));
define('APP_ENV', $_SERVER['APP_ENV']);
define('APP_DEBUG', false);

include_once(ROOT_DIR . '/src/bootstrap.php');

$container = new \Egg\Container();
$container['environment'] = \Egg\Http\Environment::create(array_merge($_SERVER, [
    'APP_ENV'              => APP_ENV,
    'APP_DEBUG'            => APP_DEBUG,
]));
$container['request'] = \Egg\Http\Request::createFromEnvironment($container['environment']);
$container['response'] = \Egg\Http\Response::createFromEnvironment($container['environment']);
registerServices($container);
registerComponents($container);

$app = new \Egg\App($container);
$app->run();