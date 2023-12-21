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

            $query = 'SELECT frdoc_number, comment_id, count, linked_responses, orgs, agencies
                FROM cache_comment_page';

            // filtering chosen column in descending or ascending order
            if (isset($queryParams['filters']['sortBy']) && isset($queryParams['filters']['sortOrder'])) {
                switch($queryParams['filters']['sortBy']) {
                    case "numberOfChanges":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'count');
                        break;
                    case "linkedResponses":
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'linked_responses');
                        break;
                }
            }

            $query .= ' LIMIT 20';

            echo $query . '<br>';

            $stmt =  $this->pdo->prepare($query);

            echo 'Before' . date("h:i:sa") . '<br>';
            $stmt->execute();

            echo 'Before fetch' . date("h:i:sa") . '<br>';
            $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo 'After fetch' . date("h:i:sa") . '<br>';

            $results = [];

            foreach ($tmp as $row) {
                $row["agencies"] = json_decode($row["agencies"]);
                $row["orgs"] = json_decode($row["orgs"]);
                $results[] = $row;
            }
            echo 'Done' . date("h:i:sa") . '<br>';


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