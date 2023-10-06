<?php

namespace App\GetInfoActions;

use App\Entities\Responses;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrgPageInfo
{

    /** @var Connection $connection */
    private Connection $connection;

    /** @var EntityManager $entityManager */
    private EntityManager $entityManager;
    public function __construct(Connection $connection, EntityManager $entityManager) {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $temp = "SELECT responses.frdoc_number, responses.response_id, responses.any_change, responses.y_prob FROM org_responses INNER JOIN responses ON org_responses.frdoc_number==responses.frdoc_number AND org_responses.response_id==responses.response_id WHERE org_responses.org_name=='" . $params['orgName'] . "'";

            $stmt = $this->connection->prepare($temp);

            $results = $stmt->executeQuery()->fetchAllAssociative();

        } catch (\Exception $e) {
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