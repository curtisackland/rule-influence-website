<?php

namespace App\GetInfoActions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetResponsesInfo extends AbstractInfoEndpoint
{
    public function __invoke(Request $request, Response $response, $params): Response
    {

        try {

            $queryParams = $request->getQueryParams();

            // TODO add date field and orgs and agencies?
            $selectQuery =  'SELECT DISTINCT frdoc_number, response_id, number_of_comments, title FROM cache_responses_page';

            $query = '';
            $boundValues = [];
            $whereClauses = [];

            if (isset($queryParams['filters']['frdocNumber'])) {
                $whereClauses[] = "frdoc_number=:frdocNumber";
                $boundValues['frdocNumber'] = $queryParams['filters']['frdocNumber'];
            }

            if (isset($queryParams['filters']['commentId'])) {
                $whereClauses[] = "comment_id=:commentId";
                $boundValues['commentId'] = $queryParams['filters']['commentId'];
            }

            if (isset($queryParams['filters']['responseId'])) {
                $whereClauses[] = "response_id=:responseId";
                $boundValues['responseId'] = $queryParams['filters']['responseId'];
            }

            if (count($whereClauses) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClauses);
            }

            // pagination
            $countQuery = 'SELECT COUNT(*) AS count FROM (SELECT  DISTINCT frdoc_number, response_id FROM cache_responses_page ' . $query . ')';
            $this->paginate($countQuery, $queryParams, $boundValues);

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "numberOfComments":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'number_of_comments');
                        break;
                }
            }

            $query .= ' LIMIT :startingLimit, :limit';
            $boundValues['startingLimit'] = $this->startingLimit;
            $boundValues['limit'] = $this->limit;

            $selectQuery .= $query;

            $records = $this->executeQuery($selectQuery, $boundValues);

            foreach ($records as $key => $value) {
                $commentsQuery = "SELECT comment_id, outcome FROM cache_responses_page 
                                    WHERE frdoc_number='" . $value['frdoc_number'] . "' AND response_id=" . $value['response_id'];

                if (isset($queryParams['filters']['outcome']) && $queryParams['filters']['outcome']) {
                    if ((int) $queryParams['filters']['outcome'] == 1) {
                        $commentsQuery .= " AND outcome=1";
                    } else {
                        $commentsQuery .= " AND outcome=0";
                    }

                }

                $commentsQuery .= ' LIMIT 10'; // Limiting the amount of linked comments on a response to 10
                
                $records[$key]['comment_data'] = $this->executeQuery($commentsQuery, []);
            }

            $results = $records;

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