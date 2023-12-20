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

            // count, linked responses, author, agencies,

            $query = 'SELECT frdoc_comments.frdoc_number, frdoc_comments.comment_id, frdoc_comments.count, count(comment_responses.response_id) FROM frdoc_comments, comment_responses';


            $query = 'SELECT json_group_array(agency) as agencies, publication_date, fr_type, frdocs.frdoc_number, title, abstract, action
                    FROM frdocs, frdoc_agencies WHERE frdocs.frdoc_number=frdoc_agencies.frdoc_number
                    GROUP BY frdocs.frdoc_number LIMIT 20';

            $stmt =  $this->pdo->prepare($query);

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
}