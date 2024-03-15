<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrgAgencies extends AbstractInfoEndpoint
{
    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $query = "SELECT
                agency,
                agency_rules,
                agency_responses,
                agency_changes,
                change_ratio,
                agency_comments
                FROM cache_org_agency";

            $countQuery = 'SELECT count(*) AS count FROM cache_org_agency';

            $whereClause = [];
            $boundValues = [];

            if (isset($params['orgName'])) {
                $whereClause[] = 'org_name=:orgName';
                $boundValues['orgName'] = $params['orgName'];
            }

            if (count($whereClause) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClause);
                $countQuery .= ' WHERE ' . join(" AND ", $whereClause);
            }

            $this->paginate($countQuery, $queryParams, $boundValues);
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "agency":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'agency');
                        break;
                    case "agency_rules":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'agency_rules');
                        break;
                    case "agency_responses":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'agency_responses');
                        break;
                    case "agency_changes":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'agency_changes');
                        break;
                    case "change_ratio":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'change_ratio');
                        break;
                    case "agency_comments":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'agency_comments');
                        break;
                }
            }

            $query .= ' LIMIT :startingLimit, :limit';
            $boundValues['startingLimit'] = $this->startingLimit;
            $boundValues['limit'] = $this->limit;

            $results = $this->executeQuery($query, $boundValues);

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