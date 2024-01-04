<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetCommentsInfo
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

            // TODO add date field when received_date is no longer null in frdoc_comments
            $selectQuery = 'SELECT frdoc_number, comment_id, number_of_changes, linked_responses, orgs, agencies, title
                FROM cache_comment_page';

            $countQuery = 'SELECT count(*) AS count FROM cache_comment_page';

            $query = '';

            $where = false;
            if (isset($queryParams['filters']['orgName']) && $queryParams['filters']['orgName']) {
                $query .= " WHERE orgs LIKE :orgName";
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
            }

            // pagination
            $page = 500;
            $limit = 10;
            if (isset($queryParams['filters']['page']) && $queryParams['filters']['page']) {
                $page = (int) $queryParams['filters']['page'] > 0 ? (int) $queryParams['filters']['page'] : 1;
            }

            if (isset($queryParams['filters']['itemsPerPage']) && $queryParams['filters']['itemsPerPage']) {
                $limit = (int) $queryParams['filters']['itemsPerPage'] > 0 ? (int) $queryParams['filters']['itemsPerPage'] : 10;
            }

            $countQuery .= $query;
            $countStmt = $this->getNumberOfResults($countQuery, $queryParams);
            $countStmt->execute();
            $totalResults = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            $totalPages = ceil($totalResults/$limit);
            $starting_limit = ($page-1) * $limit;

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

            $selectQuery .= $query;

            $selectStmt = $this->getNumberOfResults($selectQuery, $queryParams);

            $selectStmt->bindValue('startingLimit', $starting_limit);
            $selectStmt->bindValue('limit', $limit);

            $selectStmt->execute();

            $tmp = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
            $results = [];

            foreach ($tmp as $row) {
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
            'totalPages' => $totalPages
        ]));
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

    private function getNumberOfResults(string $countQuery, array $queryParams): \PDOStatement
    {
        $countStmt = $this->pdo->prepare($countQuery);

        if (isset($queryParams['filters']['orgName'])) {
            $countStmt->bindValue('orgName', '%' . $queryParams['filters']['orgName'] . '%'); // % signs for LIKE search query
        }

        if (isset($queryParams['filters']['agency'])) {
            $countStmt->bindValue('agency', '%' . $queryParams['filters']['agency'] . '%'); // % signs for LIKE search query
        }

        return $countStmt;
    }
}