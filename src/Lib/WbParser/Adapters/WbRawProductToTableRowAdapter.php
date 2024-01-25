<?php
declare(strict_types=1);

namespace App\Lib\WbParser\Adapters;

class WbRawProductToTableRowAdapter
{
    /**
     * @param array{name: string, brand: string} $rawProduct
     */
    public function __construct(
        private array $rawProduct,
    )
    {
    }

    /**
     * @return array{}
     */
    public function adapt(): array
    {
        return [
            'name' => $this->rawProduct['name'],
            'brand_name' => $this->rawProduct['brand'],
        ];
    }
}
