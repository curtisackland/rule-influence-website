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

            $selectQuery =  'SELECT frdoc_number, response_id, number_of_comments, title, any_change, text FROM cache_responses_page';

            $countQuery = 'SELECT COUNT(*) as count FROM cache_responses_page';

            $query = '';
            $boundValues = [];
            $whereClauses = [];

            if (isset($queryParams['filters']['frdocNumberOrTitle'])) {
                $whereClauses[] = "frdoc_number LIKE :frdocNumberOrTitle OR title LIKE :frdocNumberOrTitle";
                $boundValues['frdocNumberOrTitle'] = "%" . $queryParams['filters']['frdocNumberOrTitle'] . "%";
            }

            if (isset($queryParams['filters']['commentId'])) {
                $whereClauses[] = "(frdoc_number, response_id) IN (SELECT DISTINCT frdoc_number, response_id FROM comment_responses WHERE comment_id=:commentId)";
                $boundValues['commentId'] = $queryParams['filters']['commentId'];
            }

            if (isset($queryParams['filters']['responseId'])) {
                $whereClauses[] = "response_id=:responseId";
                $boundValues['responseId'] = $queryParams['filters']['responseId'];
            }

            if (isset($queryParams['filters']['resultedInChange'])) {
                if($queryParams['filters']['resultedInChange'] == 1) {
                    $whereClauses[] = "any_change='Y'";
                } elseif ($queryParams['filters']['resultedInChange'] == 0) {
                    $whereClauses[] = "any_change='N'";
                }
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
                $records[$key]['text'] = json_decode($value['text']);
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