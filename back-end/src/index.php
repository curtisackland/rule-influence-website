<?php

declare(strict_types = 1);

use App\GetInfoActions\GetCommentsInfo;
use App\GetInfoActions\GetRulesPageInfo;
use App\GetInfoActions\GetHomePageInfo;
use App\GetInfoActions\GetOrgPageInfo;
use App\GetInfoActions\GetResponsesInfo;
use App\GetInfoActions\GetOrgAgencies;
use App\GetInfoActions\GetOrgDocChanges;
use App\GetInfoActions\GetLeaderboard;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../vendor/autoload.php';

//$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . '/../../'));
//$dotenv->load();

$container = new DI\Container(include __DIR__ . '/../config/di-container.php');

$app = AppFactory::create(null, $container);

$app->get('/', GetHomePageInfo::class);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/home', GetLeaderboard::class);
    $group->get('/organization/{orgName}', GetOrgPageInfo::class);
    $group->get('/organization_agency/{orgName}', GetOrgAgencies::class);
    $group->get('/organization_doc_changes/{orgName}', GetOrgDocChanges::class);
    $group->get('/rules', GetRulesPageInfo::class);
    $group->get('/comments', GetCommentsInfo::class);
    $group->get('/responses', GetResponsesInfo::class);
    $group->get('/debug', function (Request $request, Response $response, $args) {
        $directory = __DIR__ . "/../data"; // Current directory;

        // Get an array of files and directories in the specified directory
        $files = scandir($directory);

        // Display the files
        $info = "<h2>Files in directory: $directory</h2>";
        $info .= "<ul>";

        foreach ($files as $file) {
            // Exclude the current and parent directory entries ('.' and '..')
            if ($file !== '.' && $file !== '..') {
                // Check if it is a file (not a directory)
                if (is_file($directory . '/' . $file)) {
                    $info .= "<li>$file</li>";
                }
            }
        }

        $info .= "</ul>";

        $response->getBody()->write($info);
        return $response;
    });
});

$app->run();
