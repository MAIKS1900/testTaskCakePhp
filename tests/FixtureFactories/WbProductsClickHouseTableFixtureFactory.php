<?php
declare(strict_types=1);

namespace App\Test\FixtureFactories;

use App\Lib\ClickHouse\WbProductsClickHouseTable;
use Eggheads\CakephpClickHouse\AbstractClickHouseFixtureFactory;
use Eggheads\CakephpClickHouse\AbstractClickHouseTable;

class WbProductsClickHouseTableFixtureFactory extends AbstractClickHouseFixtureFactory
{
    protected function _makeDefaultData(): array
    {
        return [
            'request_date' => '2022-01-03 00:00:00',
            'query' => 'String',
            'position' => 0,
            'name' => 'String',
            'brand_name' => 'String',
        ];
    }

    protected function _getTable(): AbstractClickHouseTable
    {
        return WbProductsClickHouseTable::getInstance();
    }
}
