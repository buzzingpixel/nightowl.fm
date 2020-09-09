<?php

declare(strict_types=1);

use App\Globals;
use App\Http\Response\Error\HttpErrorAction;
use Config\RegisterEventListeners;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\ResponseEmitter;
use Whoops\Run as WhoopsRun;

// Start session
session_start();

$rootDir = dirname(__DIR__);

// Run bootstrap and get di container
$bootstrap = require $rootDir . '/config/bootstrap.php';
$container = $bootstrap();
assert($container instanceof ContainerInterface);

// Register event listeners
$container->get(RegisterEventListeners::class)();

// Create application
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register routes
$routes = require $rootDir . '/config/Routes/index.php';
$routes($app);

// Use factory to get the ServerRequest
$request = ServerRequestCreatorFactory::create()
    ->createServerRequestFromGlobals();

Globals::setRequest($request);

// Register error handlers if Whoops does not exist
if (! class_exists(WhoopsRun::class)) {
    $errorMiddleware = $app->addErrorMiddleware(
        false,
        false,
        false
    );

    $errorMiddleware->setDefaultErrorHandler(
        $container->get(HttpErrorAction::class)
    );
}

// Register middleware
$httpMiddleWares = require $rootDir . '/config/httpAppMiddlewares.php';
$httpMiddleWares($app);

$app->addBodyParsingMiddleware();

// Emit response from app
$responseEmitter = $container->get(ResponseEmitter::class);
$responseEmitter->emit($app->handle($request));
