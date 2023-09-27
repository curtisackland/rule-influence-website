<?php

namespace App\GetInfoActions;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetHomePageInfo
{
    /** @var Connection $connection */
    private $connection;

    /** @var EntityManager $entityManager */
    private $entityManager;
    public function __construct(Connection $connection, EntityManager $entityManager) {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $stmt =  $this->connection->prepare("SELECT DISTINCT
                org_name,
                SUM(y_prob>0.5) AS y_count,
                COUNT(DISTINCT(org_responses.frdoc_number)) as n_frdocs
                
                FROM org_responses
                INNER JOIN responses
                ON (responses.frdoc_number==org_responses.frdoc_number)
                AND (responses.response_id==org_responses.response_id)
                                
                GROUP BY org_name
                ORDER BY -y_count");

            $results = $stmt->executeQuery()->fetchAllAssociative();

            /*
            $count = 0;
            foreach($results as $result) {
                if ($count != 100) {
                    echo '<pre>';
                    print_r($result);
                    echo '</pre>';
                }
                $count++;
            }
            */
        } catch (Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            return $response;
        }

        $body = $response->getBody();
        $body->write(json_encode($results, true));
        return $response->withBody($body);
    }
}