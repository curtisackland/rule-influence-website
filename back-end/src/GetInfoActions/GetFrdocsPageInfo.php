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
            file_put_contents("ULTRA_BEFORE.txt", "ULTRA_BEFORE");

            $queryParams = $request->getQueryParams();

            $query = 'SELECT json_group_array(agency) as agencies, 
                    publication_date, 
                    fr_type, 
                    frdocs.frdoc_number, 
                    title, 
                    abstract, 
                    action,
                    prevSeq.frdoc_i as prevFRDoc,
                    nextSeq.frdoc_j as nextFRDoc
                    FROM frdocs
                    LEFT JOIN frdoc_agencies ON frdocs.frdoc_number=frdoc_agencies.frdoc_number
                    LEFT JOIN frdoc_sequences prevSeq ON frdocs.frdoc_number = prevSeq.frdoc_j
                    LEFT JOIN frdoc_sequences nextSeq ON frdocs.frdoc_number = nextSeq.frdoc_i
                    LEFT JOIN (SELECT frdoc_number, COUNT(*) as responseCount FROM responses GROUP BY frdoc_number) responseCount ON frdocs.frdoc_number = responseCount.frdoc_number
                    LEFT JOIN frdoc_comments ON frdocs.frdoc_number = frdoc_comments.frdoc_number';

            $whereClause = [];
            $whereClauseValues = [];
            if (isset($queryParams['filters']['start_date'])) {
                $whereClause[] = "frdocs.publication_date > :filterStartDate";
                $whereClauseValues[] = ["filterStartDate", $queryParams['filters']['start_date']];
            }

            if (isset($queryParams['filters']['end_date'])) {
                $whereClause[] = "frdocs.publication_date < :filterEndDate";
                $whereClauseValues[] = ["filterEndDate", $queryParams['filters']['end_date']];
            }

            if (isset($queryParams['filters']['fr_type'])) {
                $whereClause[] = "frdocs.fr_type = :filterFRType";
                $whereClauseValues[] = ["filterFRType", $queryParams['filters']['fr_type']];
            }

            if (isset($queryParams['filters']['type'])) {
                $whereClause[] = "frdocs.fr_type = :filterType";
                $whereClauseValues[] = ["filterType", $queryParams['filters']['type']];
            }

            // Add where clause
            if (count($whereClause) > 0) {
                $query .= ' WHERE ' . join(" AND ", $whereClause);
            }
            $query .= ' GROUP BY frdocs.frdoc_number ORDER BY responseCount LIMIT 20';


            $stmt =  $this->pdo->prepare($query);
            // Bind all values
            foreach($whereClauseValues as $value)  {
                $stmt->bindValue($value[0], $value[1]);
            }

            file_put_contents("BEFORE.txt", "BEFORE");
            var_dump($stmt->queryString);
            $stmt->execute();
            file_put_contents("AFTER.txt", "AFTER");

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
}