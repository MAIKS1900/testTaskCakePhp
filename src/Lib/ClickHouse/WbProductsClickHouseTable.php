<?php
declare(strict_types=1);

namespace App\Lib\ClickHouse;

use Eggheads\CakephpClickHouse\AbstractClickHouseTable;

class WbProductsClickHouseTable extends AbstractClickHouseTable
{
    public const WRITER_CONFIG = 'default';
}
