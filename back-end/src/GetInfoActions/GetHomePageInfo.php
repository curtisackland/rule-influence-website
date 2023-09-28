<?php

namespace App\GetInfoActions;

use App\Entities\OrgResponses;
use App\Entities\Responses;
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
                ORDER BY -y_count
                LIMIT 10");

            $results = $stmt->executeQuery()->fetchAllAssociative();

        } catch (Exception $e) {
            $response->withStatus(500);
            $body = $response->getBody();
            $body->write(json_encode(['error' => $e->getMessage()]));
            return $response->withBody($body);
        }

        $body = $response->getBody();
        $body->write(json_encode($results));
        return $response->withBody($body);
    }
}