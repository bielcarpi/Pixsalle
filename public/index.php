<?php
declare(strict_types=1);

use DI\Container;
//use Salle\PixSalle\ErrorHandler\HttpErrorHandler;
use Salle\PixSalle\Middleware\LoginMiddleware;
use Salle\PixSalle\Middleware\StartSessionMiddleware;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

/**
 * Slim uses a dependency container to prepare, manage and inject application
 * dependencies.
 * For the application, we'll use Twig in the container
 */
$container = new Container();
require_once __DIR__ . '/../config/dependencies.php';
addDependencies($container);
AppFactory::setContainer($container);

$app = AppFactory::create();

//$callableResolver = $app->getCallableResolver();
//$responseFactory = $app->getResponseFactory();


/**
 * Add Body Parsing middleware
 */
$app->addBodyParsingMiddleware();

/**
 * Add Twig middleware
 */
$app->add(TwigMiddleware::createFromContainer($app));

/**
 * Add Routing Middleware
 * https://www.slimframework.com/docs/v4/middleware/routing.html
 */
$app->addRoutingMiddleware();
require_once __DIR__ . '/../config/routing.php';
addRoutes($app);

/**
 * Add Error middleware
 * https://www.slimframework.com/docs/v4/middleware/error-handling.html
 * TODO: Set to false false false on production
 */
$app->addErrorMiddleware(true, false, false);

/**
 * Add custom middlewares: startSession & Login (StartSession goes first!)
 */
$app->add(LoginMiddleware::class);
$app->add(StartSessionMiddleware::class);


//Run the app
$app->run();