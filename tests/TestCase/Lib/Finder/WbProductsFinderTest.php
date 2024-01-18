<?php
declare(strict_types=1);

namespace App\Test\TestCase\Lib\Finder;

use App\Lib\ClickHouse\WbProductsClickHouseTable;
use App\Lib\Finder\WbProductsFinder;
use App\Test\FixtureFactories\WbProductsClickHouseTableFixtureFactory;
use Cake\TestSuite\TestCase;

class WbProductsFinderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $items = [];
        for ($i = 0; $i < 100; $i++) {
            $items[] = [
                'request_date' => '2023-01-01 10:00:00',
                'query' => 'query 1',
                'position' => $i,
                'name' => 'old query 1 Product ' . $i,
                'brand_name' => 'old query 1 Brand ' . $i,
            ];
            $items[] = [
                'request_date' => '2024-01-01 10:00:00',
                'query' => 'query 1',
                'position' => $i,
                'name' => 'new query 1 Product ' . $i,
                'brand_name' => 'new query 1 Brand ' . $i,
            ];
            $items[] = [
                'request_date' => '2024-01-01 10:00:00',
                'query' => 'query 2',
                'position' => $i,
                'name' => 'new query 2 Product ' . $i,
                'brand_name' => 'new query 2 Brand ' . $i,
            ];
        }
        (new WbProductsClickHouseTableFixtureFactory(items: [], rowCount: 0))->persist();
        WbProductsClickHouseTable::getInstance()->insert($items);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        WbProductsClickHouseTable::getInstance()->truncate();
    }


    /**
     * Pagination - first page contains result
     *
     * @return void
     */
    public function testPaginationFirstPage()
    {
        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(1);
        $expected = $this->makeExpected('new query 1', 0);
        $this->assertEquals($expected, $result);
    }

    /**
     * Pagination - second page contains result
     * @return void
     */
    public function testPaginationSecondPage()
    {
        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(2);
        $expected = $this->makeExpected('new query 1', 50);
        $this->assertEquals($expected, $result);
    }

    /**
     * Pagination - third page without result
     * @return void
     */
    public function testPaginationThirdPageNoResult()
    {
        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(3);

        $this->assertEquals([], $result);
    }

    /**
     * @return void
     */
    public function testDifferentQueries()
    {
        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(1);

        $expected = $this->makeExpected('new query 1', 0);
        $this->assertEquals($expected, $result);

        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 2')
            ->getPage(1);

        $expected = $this->makeExpected('new query 2', 0);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testLastQueryData()
    {
        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(1);

        $expected = $this->makeExpected('new query 1', 0);
        $this->assertEquals($expected, $result);

        $items = [];
        for ($i = 0; $i < 100; $i++) {
            $items[] = [
                'request_date' => '2025-01-01 10:00:00',
                'query' => 'query 1',
                'position' => $i,
                'name' => 'newer query 1 Product ' . $i,
                'brand_name' => 'newer query 1 Brand ' . $i,
            ];
        }
        (new WbProductsClickHouseTableFixtureFactory(items: [], rowCount: 0))->persist();
        WbProductsClickHouseTable::getInstance()->insert($items);

        $finder = new WbProductsFinder();
        $result = $finder->setQuery('query 1')
            ->getPage(1);

        $expected = $this->makeExpected('newer query 1', 0);
        $this->assertEquals($expected, $result);
    }

    private function makeExpected(string $namePrefix, $start): array
    {
        $expected = [];
        for ($i = $start; $i < $start + 50; $i++) {
            $expected[] = [
                'position' => $i,
                'name' => $namePrefix . ' Product ' . $i,
                'brand_name' => $namePrefix . ' Brand ' . $i,
            ];
        }

        return $expected;
    }
}
