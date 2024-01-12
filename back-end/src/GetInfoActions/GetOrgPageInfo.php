<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrgPageInfo
{

    /** @var PDO $pdo */
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response, $params): Response
    {
        try {

            $query = "SELECT
                comment_id,
                y_prob_avg,
                total_response_count,
                total_rules_changed
                FROM cache_org_page
                WHERE org_name=:orgName
                LIMIT 10";

            $stmt = $this->pdo->prepare($query);

            // for binding values at runtime to prevent SQL injection
            $stmt->bindValue('orgName', $params['orgName']);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response;
        }
        $body = $response->getBody();
        $body->write(json_encode($results));
        return $response->withBody($body);
    }
}