<?php declare(strict_types=1);

use League\Route\Router;
use League\Route\RouteGroup;
use League\Container\Container;
use Bogatyrev\PhoneNumberFactory;
use Symfony\Component\Dotenv\Dotenv;
use League\Route\Strategy\JsonStrategy;
use Zend\Diactoros\ServerRequestFactory;
use Http\Factory\Diactoros\ResponseFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Bogatyrev\controllers\PhoneNumbersController;
use Bogatyrev\repositories\PhoneNumberRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

include '../vendor/autoload.php';

// Import ENVs
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Configure di container
$container = new Container;

// Db connection
$container->add(PDO::class, function() {
    $dbName = getenv('MYSQL_DB');
    $host = getenv('MYSQL_HOST');
    return new PDO("mysql:dbname=$dbName;host=$host", getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}, true);

$container->add(PhoneNumberFactory::class);

$container->add(PhoneNumberRepository::class)
    ->addArgument(PDO::class)
    ->addArgument(LoggerInterface::class)
    ->addArgument(PhoneNumberFactory::class);

$container->add(PhoneNumbersController::class)
    ->addArgument(PhoneNumberRepository::class)
    ->addArgument(PhoneNumberFactory::class);

// Add logger
$container->add(LoggerInterface::class, function() {
    $logger = new Logger('log');
    $logsDir = getenv('LOGS_DIR') === false ? __DIR__ . '/../logs' : getenv('LOGS_DIR');
    $logger->pushHandler(new StreamHandler($logsDir . '/app.log', Logger::ERROR));
    return $logger;
}, true);

$responseFactory = new ResponseFactory;
$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

/** @var League\Route\Strategy\JsonStrategy $strategy*/
$strategy = (new JsonStrategy($responseFactory))->setContainer($container);

/** @var League\Route\Router $router */
$router  = (new Router)->setStrategy($strategy);

// API
$router->group('/v1', function (RouteGroup $route) {
    $route->get('/phone-numbers', [PhoneNumbersController::class, 'listItems']);
    $route->get('/phone-numbers/{id:number}', [PhoneNumbersController::class, 'retrieveItem']);
    $route->post('/phone-numbers', [PhoneNumbersController::class, 'createItem']);
    $route->put('/phone-numbers/{id:number}', [PhoneNumbersController::class, 'updateItem']);
    $route->delete('/phone-numbers/{id:number}', [PhoneNumbersController::class, 'deleteItem']);
    // $route->options('/phone-numbers/{id:number}', [PhoneNumbersController::class, 'deleteItem']);
});

$response = $router->dispatch($request);

// send the response to the browser
(new SapiEmitter)->emit($response);
