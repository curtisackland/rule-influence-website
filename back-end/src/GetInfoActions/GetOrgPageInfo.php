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
            $queryParams = $request->getQueryParams();

            $query = "SELECT comment_responses.comment_id, comment_responses.frdoc_number, comment_responses.response_id, comment_responses.score, comment_responses.norm_score
                     FROM comment_responses INNER JOIN (SELECT comment_id FROM comment_orgs WHERE org_name='" . $params['orgName'] . "' LIMIT 10) corgs ON corgs.comment_id==comment_responses.comment_id";

            if (isset($queryParams['filters']['commentID']) && $queryParams['filters']['commentID']) {
                $query .= " WHERE comment_responses.comment_id LIKE :commentID";
            }

            if (isset($queryParams['filters']['frdocNumber']) && $queryParams['filters']['frdocNumber']) {
                $query .= " WHERE comment_responses.frdoc_number LIKE :frdocNumber";
            }

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "responseID":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'comment_responses.response_id');
                        break;
                    case "score":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'comment_responses.score');
                        break;
                    case "normScore":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'comment_responses.norm_score');
                        break;
                }
            }

            $query .= " LIMIT 10";

            $stmt = $this->connection->prepare($query);

            // for binding values at runtime to prevent SQL injection
            if (isset($queryParams['filters']['commentID'])) {
                $stmt->bindValue('commentID', '%' . $queryParams['filters']['commentID'] . '%'); // % signs for LIKE search query
            }

            if (isset($queryParams['filters']['frdocNumber'])) {
                $stmt->bindValue('frdocNumber', '%' . $queryParams['filters']['frdocNumber'] . '%'); // % signs for LIKE search query
            }

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

    /**
     * Creates an ORDER BY query based on if $sortOrder is descending or ascending and sorts it on the column given by $sortBy
     * @param string $sortOrder
     * @param string $sortBy
     * @return string|void
     */
    private function sortOrder(string $sortOrder, string $sortBy) {
        if ($sortOrder == 'DESC') {
            return ' ORDER BY ' . $sortBy . ' DESC';
        } elseif ($sortOrder == 'ASC') {
            return ' ORDER BY ' . $sortBy . ' ASC';
        }
    }
}