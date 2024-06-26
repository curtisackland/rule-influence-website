<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetRulesPageInfo extends AbstractInfoEndpoint
{

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {

            $queryParams = $request->getQueryParams();

            $selectQuery = 'SELECT agencies, 
                    publication_date, 
                    fr_type,
                    type,
                    frdoc_number,
                    title,
                    abstract,
                    action,
                    prevFRDoc,
                    nextFRDoc,
                    response_count,
                    comment_count,
                    change_count,
                    orgs
                    FROM cache_frdocs_page';

            $countQuery = 'SELECT count(*) AS count FROM cache_frdocs_page';

            $query = '';

            $whereClause = [];
            $boundValues = [];
            if (isset($queryParams['filters']['start_date'])) {
                $whereClause[] = "publication_date >= :filterStartDate";
                $boundValues['filterStartDate'] = $queryParams['filters']['start_date'];
            }

            if (isset($queryParams['filters']['end_date'])) {
                $whereClause[] = "publication_date <= :filterEndDate";
                $boundValues['filterEndDate'] = $queryParams['filters']['end_date'];
            }

            if (isset($queryParams['filters']['fr_type'])) {
                $whereClause[] = "fr_type = :filterFRType";
                $boundValues['filterFRType'] = $queryParams['filters']['fr_type'];
            }

            if (isset($queryParams['filters']['type'])) {
                $whereClause[] = "type = :filterType";
                $boundValues['filterType'] = $queryParams['filters']['type'];
            }

            if (isset($queryParams['filters']['titleOrId'])) {
                $whereClause[] = '(frdoc_number LIKE :titleOrId OR title LIKE :titleOrId)';
                $boundValues['titleOrId'] = '%' . $queryParams['filters']['titleOrId'] . '%';
            }

            if (isset($queryParams['filters']['idArray']) and !empty($queryParams['filters']['idArray'])) {
                $inArray = [];
                foreach ($queryParams['filters']['idArray'] as $index => $id) {
                    $queryVar = 'idArray_' . $index;
                    $inArray[] = ':' . $queryVar;
                    $boundValues[$queryVar] = $queryParams['filters']['idArray'][$index];
                }
                $whereClause[] = 'frdoc_number IN (' . join(",", $inArray) . ')';
            }

            if (isset($queryParams['filters']['commentId'])) {
                $whereClause[] = "frdoc_number IN (SELECT DISTINCT frdoc_number FROM frdoc_comments WHERE comment_id = :commentId) OR frdoc_number IN (SELECT DISTINCT frdoc_number FROM frdoc_input_comments WHERE comment_id = :commentId)";
                $boundValues['commentId'] = $queryParams['filters']['commentId'];
            }

            if (isset($queryParams['filters']['agency'])) {
                $whereClause[] = "agencies LIKE :agency";
                $boundValues['agency'] = '%' . $queryParams['filters']['agency'] . '%';
            }

            if (isset($queryParams['filters']['org'])) {
                $whereClause[] = "orgs LIKE :org";
                $boundValues['org'] = '%' . $queryParams['filters']['org'] . '%';
            }

            // Add where clause
            if (count($whereClause) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClause);
            }

            $countQuery .= $query;
            $this->paginate($countQuery, $queryParams, $boundValues);

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "date":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'publication_date');
                        break;
                    case "num_comments":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'comment_count');
                        break;
                    case "num_responses":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'response_count');
                        break;
                    case "num_changes":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'change_count');
                        break;
                }
            }

            $query .= ' LIMIT :startingLimit, :limit';
            $boundValues['startingLimit'] = $this->startingLimit;
            $boundValues['limit'] = $this->limit;

            $selectQuery .= $query;
            $records = $this->executeQuery($selectQuery, $boundValues);

            $results = [];
            // Decode JSON arrays
            foreach ($records as $row) {
                $row["agencies"] = json_decode($row["agencies"]);

                // Filter removes null entries from array
                $row["prevFRDoc"] = array_filter(json_decode($row["prevFRDoc"]));
                $row["nextFRDoc"] = array_filter(json_decode($row["nextFRDoc"]));

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