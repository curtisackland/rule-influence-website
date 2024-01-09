<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetHomePageInfo extends AbstractInfoEndpoint
{

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $selectQuery = 'SELECT
                org_name,
                y_count,
                n_frdocs
                FROM cache_home_page';

            $countQuery = 'SELECT count(*) as count FROM cache_home_page';

            $query = '';
            $boundValues = [];

            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE org_name LIKE :orgName";
                $boundValues['orgName'] = '%' . $queryParams['filters']['orgName'] . '%'; // % signs for LIKE search query
            }

            $countQuery .= $query;
            $this->paginate($countQuery, $queryParams, $boundValues);

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