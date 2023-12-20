<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrgPageInfo
{

    /** @var PDO $pdo */
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $query = "SELECT
                comment_id,
                frdoc_number,
                response_id,
                score,
                norm_score
                FROM cache_org_page
                WHERE org_name=:orgName";

            if (isset($queryParams['filters']['commentID']) && $queryParams['filters']['commentID']) {
                $query .= " AND comment_id LIKE :commentID";
            }

            if (isset($queryParams['filters']['frdocNumber']) && $queryParams['filters']['frdocNumber']) {
                $query .= " AND frdoc_number LIKE :frdocNumber";
            }

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "responseID":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'response_id');
                        break;
                    case "score":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'score');
                        break;
                    case "normScore":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'norm_score');
                        break;
                }
            }

            $query .= " LIMIT 10";

            $stmt = $this->pdo->prepare($query);

            // for binding values at runtime to prevent SQL injection
            $stmt->bindValue('orgName', $params['orgName']);

            if (isset($queryParams['filters']['commentID'])) {
                $stmt->bindValue('commentID', '%' . $queryParams['filters']['commentID'] . '%'); // % signs for LIKE search query
            }

            if (isset($queryParams['filters']['frdocNumber'])) {
                $stmt->bindValue('frdocNumber', '%' . $queryParams['filters']['frdocNumber'] . '%'); // % signs for LIKE search query
            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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