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
            $selectQuery = 'SELECT comment_id, number_of_changes, linked_responses, orgs, agencies, title, receive_date
                FROM cache_comment_page';

            $countQuery = 'SELECT count(*) AS count FROM cache_comment_page';

            $query = '';
            $boundValues = [];
            $whereClauses = [];

            if (isset($queryParams['filters']['commentId']) && $queryParams['filters']['commentId']) {
                $whereClauses[] .= "comment_id=:commentId";
                $boundValues['commentId'] = $queryParams['filters']['commentId'];
            }

            if (isset($queryParams['filters']['frdocNumber']) && $queryParams['filters']['frdocNumber']
                && isset($queryParams['filters']['responseId']) && $queryParams['filters']['responseId']
            ) {
                $whereClauses[] .= "(comment_id) IN (SELECT DISTINCT comment_id FROM comment_responses WHERE frdoc_number=:frdocNumber AND response_id=:responseId)";
                $boundValues['frdocNumber'] = $queryParams['filters']['frdocNumber'];
                $boundValues['responseId'] = $queryParams['filters']['responseId'];
            } elseif (isset($queryParams['filters']['frdocNumber']) && $queryParams['filters']['frdocNumber']) {
                $whereClauses[] .= "(comment_id) IN (SELECT DISTINCT comment_id FROM comment_responses WHERE frdoc_number=:frdocNumber)";
                $boundValues['frdocNumber'] = $queryParams['filters']['frdocNumber'];
            }

            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $whereClauses[] .= "orgs LIKE :orgName";
                $boundValues['orgName'] = '%' . $queryParams['filters']['orgName'] . '%';
            }

            if (isset($queryParams['filters']['agency']) && $queryParams['filters']['agency']) {
                $whereClauses[] .= "agencies LIKE :agency";
                $boundValues['agency'] = '%' . $queryParams['filters']['agency'] . '%';
            }

            if (isset($queryParams['filters']['startDate']) && $queryParams['filters']['startDate']) {
                $whereClauses[] .= "receive_date >= :startDate";
                $boundValues['startDate'] = $queryParams['filters']['startDate'];
            }

            if (isset($queryParams['filters']['endDate']) && $queryParams['filters']['endDate']) {
                $whereClauses[] .= "receive_date <= :endDate";
                $boundValues['endDate'] = $queryParams['filters']['endDate'];
            }

            if (count($whereClauses) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClauses);
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
                    case "date":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'receive_date');
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