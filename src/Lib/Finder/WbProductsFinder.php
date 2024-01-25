<?php
declare(strict_types=1);

namespace App\Lib\Finder;

use App\Lib\ClickHouse\WbProductsClickHouseTable;

class WbProductsFinder
{
    protected string $query;

    protected int $pageSize = 50;

    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param int $page - greater or equal 1
     * @return array
     */
    public function getPage(int $page): array
    {
        $table = WbProductsClickHouseTable::getInstance();
        return $table->select("
            SELECT
                position as position,
                name as name,
                brand_name as brand_name
            FROM {table}
            WHERE query = :query
                AND request_date = (SELECT MAX(request_date) FROM {table} WHERE query = :query)
            ORDER BY position
            LIMIT :page, :pageSize", [
            'table' => $table->getTableName(),
            'query' => $this->query,
            'page' => ($page - 1) * $this->pageSize,
            'pageSize' => $this->pageSize
        ])->rows();

    }
}
