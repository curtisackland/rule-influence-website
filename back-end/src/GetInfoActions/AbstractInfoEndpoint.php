<?php

namespace App\GetInfoActions;

use PDO;

class AbstractInfoEndpoint
{
    protected PDO $pdo;

    /** @var int starting point of pagination */
    protected int $startingLimit;

    /** @var int limit of how many items in a page */
    protected int $limit;

    /** @var int total number of pages */
    protected int $totalPages;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * @param string $query
     * @param array $queryParams
     * @param array $boundValues
     * @return void
     * Does necessary calculations for pagination. The resulting column with the count should be named 'count'.
     * Example count query with the resulting column being named count: SELECT count(*) AS count FROM some_table
     */
    protected function paginate(string $query, array $queryParams, array $boundValues): void
    {
        // pagination
        $page = 1;
        $this->limit = 10;
        if (isset($queryParams['filters']['page']) && $queryParams['filters']['page']) {
            $page = (int) $queryParams['filters']['page'] > 0 ? (int) $queryParams['filters']['page'] : 1;
        }

        if (isset($queryParams['filters']['itemsPerPage']) && $queryParams['filters']['itemsPerPage']) {
            $this->limit = (int) $queryParams['filters']['itemsPerPage'] > 0 ? (int) $queryParams['filters']['itemsPerPage'] : 10;
        }

        $totalResults = $this->executeQuery($query, $boundValues)[0]['count'];
        $this->totalPages = ceil($totalResults/$this->limit);
        $this->startingLimit = ($page-1) * $this->limit;
    }

    /**
     * @param string $query
     * @param array $boundValues
     * @return array Returns a PDO statement
     * Returns a PDO statement
     */
    protected function executeQuery(string $query, array $boundValues): array
    {
        $stmt = $this->pdo->prepare($query);

        foreach ($boundValues as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates an ORDER BY query based on if $sortOrder is descending or ascending and sorts it on the column given by $sortBy
     * @param string $sortOrder
     * @param string $sortBy
     * @return string|void
     */
    protected function sortOrder(string $sortOrder, string $sortBy) {
        if ($sortOrder == 'DESC') {
            return ' ORDER BY ' . $sortBy . ' DESC';
        } elseif ($sortOrder == 'ASC') {
            return ' ORDER BY ' . $sortBy . ' ASC';
        }
    }
}