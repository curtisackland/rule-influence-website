<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetCommentsInfo extends AbstractInfoEndpoint
{

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {

            $queryParams = $request->getQueryParams();

            // TODO add date field when received_date is no longer null in frdoc_comments
            $selectQuery = 'SELECT frdoc_number, comment_id, number_of_changes, linked_responses, orgs, agencies, title
                FROM cache_comment_page';

            $countQuery = 'SELECT count(*) AS count FROM cache_comment_page';

            $query = '';
            $boundValues = [];

            $where = false;
            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE orgs LIKE :orgName";
                $boundValues['orgName'] = '%' . $queryParams['filters']['orgName'] . '%';
                $where = true;
            }

            if (isset($queryParams['filters']['agency']) && $queryParams['filters']['agency']) {
                if ($where) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $where = true;
                }
                $query .= " agencies LIKE :agency";
                $boundValues['agency'] = '%' . $queryParams['filters']['agency'] . '%';
            }

            // pagination
            $countQuery .= $query;
            $this->paginate($countQuery, $queryParams, $boundValues);

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "numberOfChanges":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'number_of_changes');
                        break;
                    case "linkedResponses":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'linked_responses');
                        break;
                }
            }

            $query .= ' LIMIT :startingLimit, :limit';
            $boundValues['startingLimit'] = $this->startingLimit;
            $boundValues['limit'] = $this->limit;

            $selectQuery .= $query;

            $records = $this->executeQuery($selectQuery, $boundValues);

            $results = [];
            foreach ($records as $row) {
                $row["agencies"] = json_decode($row["agencies"]);
                $row["orgs"] = json_decode($row["orgs"]);
                $results[] = $row;
            }

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