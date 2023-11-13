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

            $queryParams = $request->getQueryParams();

            $query = 'SELECT DISTINCT
                org_name,
                SUM(y_prob>0.5) AS y_count,
                COUNT(DISTINCT(org_responses.frdoc_number)) as n_frdocs
                FROM org_responses
                INNER JOIN responses
                ON (responses.frdoc_number==org_responses.frdoc_number)
                AND (responses.response_id==org_responses.response_id)';

            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE org_name LIKE :orgName";
            }

            $query .= ' GROUP BY org_name';

            // filtering chosen column from highest to lowest or lowest to highest
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "orgName":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'org_name');
                        break;
                    case "yCount":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'y_count');
                        break;
                    case "frdocs":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'n_frdocs');
                        break;
                }
            }

            $query .= ' LIMIT 10';

            $stmt =  $this->connection->prepare($query);

            // for binding values at runtime to prevent SQL injection
            if (isset($queryParams['filters']['orgName'])) {
                $stmt->bindValue('orgName', '%' . $queryParams['filters']['orgName'] . '%'); // % signs for LIKE search query
            }

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

    /**
     * Creates an ORDER BY query based on if $sortOrder is highest or lowest and sorts it on the column given by $sortBy
     * @param string $sortOrder
     * @param string $sortBy
     * @return string|void
     */
    private function sortOrder(string $sortOrder, string $sortBy) {

        if ($sortBy == 'org_name') {
            if ($sortOrder == "highest") {
                return ' ORDER BY ' . $sortBy . ' ASC';
            } elseif ($sortOrder == "lowest") {
                return ' ORDER BY ' . $sortBy . ' DESC';
            }
        }

        if ($sortOrder == "highest") {
            return ' ORDER BY -' . $sortBy;
        } elseif ($sortOrder == "lowest") {
            return ' ORDER BY ' . $sortBy;
        }
    }
}