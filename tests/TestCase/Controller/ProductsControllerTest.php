<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Lib\Finder\WbProductsFinder;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TestingController Test Case
 *
 * @uses \App\Controller\ProductsController
 */
class ProductsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableCsrfToken();

        $this->mockService(WbProductsFinder::class, function () {
            return new class extends WbProductsFinder {
                public function getPage(int $page): array
                {
                    if ($this->query === 'no data') {
                        return [];
                    }
                    if ($page > 2) {
                        return [];
                    }
                    return array_map(function ($i) use ($page) {
                        $start = ($page - 1) * $this->pageSize;
                        return [
                            'position' => $start + $i,
                            'name' => 'name ' . $start + $i,
                            'brand_name' => 'brand_name' . $start + $i,
                        ];
                    }, range(0, $this->pageSize));
                }
            };
        });
    }

    /**
     * Validation - forbidden pages
     *
     * @return void
     */
    public function testValidationWrongPage()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'query 1',
            'page' => 0,
        ]);

        $this->assertResponseCode(400);
        $this->assertResponseContains('error');
    }

    /**
     * Pagination
     *
     * @return void
     */
    public function testPagination()
    {
        # Page 1 with data
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'query 1',
            'page' => 1,
        ]);
        $this->assertResponseOk();
        $expected = $this->makeExpectedResponseBody(0);
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody(), true));

        # Page 2 with data
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'query 1',
            'page' => 2,
        ]);
        $this->assertResponseOk();
        $expected = $this->makeExpectedResponseBody(50);
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody(), true));

        # Page 3 without data
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'query 1',
            'page' => 3,
        ]);
        $this->assertResponseOk();
        $expected = ['products' => []];
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody(), true));
    }

    /**
     * Data not found.
     *
     * @return void
     */
    public function testNoQueryData()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'has data',
            'page' => 1,
        ]);
        $this->assertResponseOk();
        $expected = $this->makeExpectedResponseBody(0);
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody(), true));

        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
        $this->post('/products/find', [
            'query' => 'no data',
            'page' => 1,
        ]);
        $this->assertResponseOk();
        $expected = ['products' => []];
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody(), true));
    }

    private function makeExpectedResponseBody($start): array
    {
        return ['products' => array_map(function ($i) use ($start) {
            return [
                'position' => $start + $i,
                'name' => 'name ' . $start + $i,
                'brand_name' => 'brand_name' . $start + $i,
            ];
        }, range(0, 50))];
    }
}
