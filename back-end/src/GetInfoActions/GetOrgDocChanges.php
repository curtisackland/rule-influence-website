<?php

namespace App\GetInfoActions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrgDocChanges
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
                frdoc_number,
                sumScore
                FROM cache_org_doc_changes
                WHERE org_name=:orgName
                ORDER BY sumScore DESC
                LIMIT 3";

            $stmt = $this->pdo->prepare($query);

            // for binding values at runtime to prevent SQL injection
            $stmt->bindValue('orgName', $params['orgName']);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $key => $value) {
                $frdocQuery = "SELECT title, agencies FROM cache_frdocs_page WHERE frdoc_number='" . $value["frdoc_number"] . "'";

                $stmt = $this->pdo->prepare($frdocQuery);
                $stmt->execute();
                $frdocData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $results[$key]["title"] = $frdocData[0]["title"];
                $results[$key]["agencies"] = json_decode($frdocData[0]["agencies"]);
            }

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