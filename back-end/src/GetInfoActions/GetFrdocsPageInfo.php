<?php

namespace App\GetInfoActions;

use App\Entities\OrgResponses;
use App\Entities\Responses;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetFrdocsPageInfo
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

            $queryParams = $request->getQueryParams();

            $query = 'SELECT json_group_array(agency), publication_date, fr_type, frdocs.frdoc_number, title, abstract, action
                    FROM frdocs, frdoc_agencies WHERE frdocs.frdoc_number=frdoc_agencies.frdoc_number
                    GROUP BY frdocs.frdoc_number LIMIT 20';

            $stmt =  $this->connection->prepare($query);

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