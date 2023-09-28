<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Psr\Container\ContainerInterface;

$definitions = [
    Connection::class => function (ContainerInterface $container) {
        $connectionParams = [
            'path' => __DIR__ . '/../data/rulemaking_influence.db',
            'memory' => false,
            'driver' => 'pdo_sqlite'
        ];
        return DriverManager::getConnection($connectionParams);
    },
    EntityManager::class => function (ContainerInterface $container) {
        $config = new Configuration();
        $entityFilePaths = [__DIR__ . '/../src/Entities/'];
        $driver = new AttributeDriver($entityFilePaths, true);
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(__DIR__ . '/../src/Proxies');
        $config->setProxyNamespace('App\Proxies');

        return new EntityManager($container->get(Connection::class), $config);
    }
];

return $definitions;