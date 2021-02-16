<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

define('ROOT_DIR', dirname(__DIR__));
define('APP_ENV', 'dev');
define('APP_DEBUG', true);

include_once(ROOT_DIR . '/src/bootstrap.php');