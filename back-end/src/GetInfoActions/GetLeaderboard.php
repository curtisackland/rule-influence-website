<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetLeaderboard extends AbstractInfoEndpoint
{

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $selectQuery = 'SELECT
                org_name,
                org_responses,
                org_changes,
                org_rules
                FROM cache_leaderboard';

            $countQuery = 'SELECT count(*) as count FROM cache_leaderboard';

            $query = '';
            $boundValues = [];

            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE org_name LIKE :orgName";
                $boundValues['orgName'] = '%' . $queryParams['filters']['orgName'] . '%'; // % signs for LIKE search query
            }

            // Apply sorting
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "org_name":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'org_name');
                        break;
                    case "org_responses":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'org_responses');
                        break;
                    case "org_changes":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'org_changes');
                        break;
                    case "org_rules":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'org_rules');
                        break;
                }
            }

            $countQuery .= $query;
            $this->paginate($countQuery, $queryParams, $boundValues);

            $query .= ' LIMIT :startingLimit, :limit';
            $boundValues['startingLimit'] = $this->startingLimit;
            $boundValues['limit'] = $this->limit;

            $selectQuery .= $query;

            $results = $this->executeQuery($selectQuery, $boundValues);

        } catch (Exception $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response;
        }

        $body = $response->getBody();
        $body->write(json_encode([
            'data' => $results,
            'totalPages' => $this->totalPages
        ]));
        return $response->withBody($body);
    }
}
