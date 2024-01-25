<?php
declare(strict_types=1);

namespace App\Lib\WbParser;

class WbProductParseConfig
{
    public function __construct(
        public string $query,
        public int $limit = 1000,
    )
    {
    }
}
