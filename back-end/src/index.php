<?php

declare(strict_types = 1);

echo 'hello';
/*
use App\Controllers\HomeController;
use App\DB;
use App\Download;
use App\Leaderboards\GetLeaderboards;
//use App\Users\Users;
use App\Users\GetUsers;
use App\Users\NewUser;
use App\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use App\Test\TestRoute;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . '/../../'));
$dotenv->load();

define('STORAGE_PATH', __DIR__ . '/../storage');
define('VIEW_PATH', __DIR__ . '/views');

$container = new DI\Container(include __DIR__ . '/../config/di-container.php');

$app = AppFactory::create(null, $container);

$app->get('/', function (Request $request, Response $response, $args) {
    echo View::make('index');
    //$response->getBody()->write("Home");
    return $response;
});

$app->run();
*/