<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;

/**
 * Define some useful constants
 */
define('BASE_PATH', dirname(__DIR__));
// Must contain trailing slash!
define('APP_PATH', BASE_PATH . '/');

$di = new FactoryDefault();

// Include services
include APP_PATH . "config/diServices.php";

// Include loaders
include APP_PATH . 'config/loader.php';

// Create an application
$application = new Application($di);

// Include modules
require APP_PATH . 'config/modules.php';

try {
    // Handle the request
    $response = $application->handle();

    $response->send();
} catch (\Exception $e) {
    $logger = new \Phalcon\Logger\Adapter\File(APP_PATH.'/logs/exceptionator.log');

    $msg = get_class($e). ": ". $e->getMessage(). "\n"
    . " File=". $e->getFile(). "\n"
    . " Line=". $e->getLine(). "\n"
    . $e->getTraceAsString();


    $logger->error($msg);
    echo $e->getMessage();
}