<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetHomePageInfo
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

            $query = 'SELECT DISTINCT
                org_name,
                y_count,
                n_frdocs
                FROM cache_home_page';

            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE org_name LIKE :orgName";
            }

            // filtering chosen column in descending or ascending order
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

            $stmt =  $this->pdo->prepare($query);

            // for binding values at runtime to prevent SQL injection
            if (isset($queryParams['filters']['orgName'])) {
                $stmt->bindValue('orgName', '%' . $queryParams['filters']['orgName'] . '%'); // % signs for LIKE search query
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