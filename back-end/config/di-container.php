<?php

use Psr\Container\ContainerInterface;

$definitions = [
    PDO::class => function (ContainerInterface $container) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../data/rulemaking_influence.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_TIMEOUT, 2);
        return $pdo;
    }
];

return $definitions;