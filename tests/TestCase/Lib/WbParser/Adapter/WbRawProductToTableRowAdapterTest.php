<?php

namespace App\Test\TestCase\Lib\WbParser\Adapter;

use App\Lib\WbParser\Adapters\WbRawProductToTableRowAdapter;
use Cake\TestSuite\TestCase;

class WbRawProductToTableRowAdapterTest extends TestCase
{
    public function testAdapt()
    {
        $rawProduct = [
            'name' => 'productName',
            'brand' => 'productBrandName',
        ];
        $expected = [
            'name' => 'productName',
            'brand_name' => 'productBrandName',
        ];

        $adapter = new WbRawProductToTableRowAdapter($rawProduct);

        $this->assertEquals($expected, $adapter->adapt());
    }
}
