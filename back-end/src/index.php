<?php

declare(strict_types = 1);

use App\GetInfoActions\GetHomePageInfo;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use App\Test\TestRoute;

require_once __DIR__ . '/../vendor/autoload.php';

//$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . '/../../'));
//$dotenv->load();

$container = new DI\Container(include __DIR__ . '/../config/di-container.php');

$app = AppFactory::create(null, $container);

$app->get('/', GetHomePageInfo::class);
// Test route for messing around
$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/{garbage}', TestRoute::class);
});

$app->run();
