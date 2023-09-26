<?php

namespace App\Test;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestRoute
{
    public function __invoke(Request $request, Response $response, $params): Response
    {
        echo __DIR__;
        //var_dump($params);
        echo 'yoooooooooooooooooooooooooooooooooo';
        //$response->getBody()->write(var_dump($var));
        return $response;
    }
}