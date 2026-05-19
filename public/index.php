<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use App\Middleware\ErrorHandlerMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\JwtAuthentication;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/Config/settings.php';
$containerBuilder->addDefinitions($settings);

// Set up dependencies
$dependenciesFactory = require __DIR__ . '/../app/Config/dependencies.php';
$dependenciesFactory($containerBuilder);

// Build Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Force DB initialization (Eloquent)
$container->get('db');



// Set Base Path to handle subdirectories in XAMPP
$app->setBasePath((function () {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname($scriptName));
    return $basePath === '/' ? '' : $basePath;
})());

// Add Middlewares
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->add(new \Slim\Middleware\MethodOverrideMiddleware());

// Add Custom Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $container->get('settings')['displayErrorDetails'],
    $container->get('settings')['logError'],
    $container->get('settings')['logErrorDetails'],
    $container->get('logger')
);
$errorMiddleware->setDefaultErrorHandler(new ErrorHandlerMiddleware($app));

// Add CORS Middleware (Outer most middleware)
$app->add(new \App\Middleware\CorsMiddleware());

// Define Routes
$routes = require __DIR__ . '/../app/Routes/api.php';
$routes($app);

$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to the registroUx-api',
        'status' => 'active'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/health', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
