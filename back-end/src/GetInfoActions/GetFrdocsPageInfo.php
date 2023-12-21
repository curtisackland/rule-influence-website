<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetFrdocsPageInfo
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

            $query = 'SELECT agencies, 
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
                    change_count
                    FROM cache_frdocs_page';

            $whereClause = [];
            $whereClauseValues = [];
            if (isset($queryParams['filters']['start_date'])) {
                $whereClause[] = "publication_date > :filterStartDate";
                $whereClauseValues[] = ["filterStartDate", $queryParams['filters']['start_date']];
            }

            if (isset($queryParams['filters']['end_date'])) {
                $whereClause[] = "publication_date < :filterEndDate";
                $whereClauseValues[] = ["filterEndDate", $queryParams['filters']['end_date']];
            }

            if (isset($queryParams['filters']['fr_type'])) {
                $whereClause[] = "fr_type = :filterFRType";
                $whereClauseValues[] = ["filterFRType", $queryParams['filters']['fr_type']];
            }

            if (isset($queryParams['filters']['type'])) {
                $whereClause[] = "type = :filterType";
                $whereClauseValues[] = ["filterType", $queryParams['filters']['type']];
            }

            // Add where clause
            if (count($whereClause) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClause);
            }

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
                        $query .= $this->sortOrder($queryParams['filters']['sortOrder'], 'NYI');
                        break;
                }
            }
            $query .= ' LIMIT 20';

            $stmt =  $this->pdo->prepare($query);
            // Bind all values
            foreach($whereClauseValues as $value)  {
                $stmt->bindValue($value[0], $value[1]);
            }

            //var_dump($stmt->queryString);
            $stmt->execute();

            $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];

            foreach ($tmp as $row) {
                $row["agencies"] = json_decode($row["agencies"]);
                $results[] = $row; 
            }



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