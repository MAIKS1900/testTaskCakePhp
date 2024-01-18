<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Lib\ClickHouse\WbProductsClickHouseTable;
use App\Lib\WbParser\WbProductParser;
use App\Test\FixtureFactories\WbProductsClickHouseTableFixtureFactory;
use Cake\Console\CommandInterface;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Command\ParseProductsCommand Test Case
 *
 * @uses \App\Command\ParseProductsCommand
 */
class ParseProductsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        (new WbProductsClickHouseTableFixtureFactory(items: [], rowCount: 0))->persist();
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \App\Command\ParseProductsCommand::execute()
     */
    public function testInsert(): void
    {
        $table = WbProductsClickHouseTable::getInstance();

        $this->mockService(WbProductParser::class, function () {
            return new class extends WbProductParser {
                public function parse(): array
                {
                    return [
                        [
                            'name' => 'productName',
                            'brand' => 'productBrandName',
                        ],
                        [
                            'name' => 'productName1',
                            'brand' => 'productBrandName1',
                        ],
                    ];
                }
            };
        });

        $this->exec('parse_products queryStr');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $rows = $table->select("SELECT * FROM {table}", ['table' => $table->getTableName()])->rows();
        $this->assertEquals(2, count($rows));

        # В обе строки записан поисковый запрос указанный при вызове команды
        # Первая строка имеет позицию 0 и данные первого продукта
        $row = $rows[0];
        $this->assertEquals('queryStr', $row['query']);
        $this->assertEquals(0, $row['position']);
        $this->assertEquals('productName', $row['name']);
        $this->assertEquals('productBrandName', $row['brand_name']);

        # Вторая строка имеет позицию 1 и данные второго продукта
        $row = $rows[1];
        $this->assertEquals('queryStr', $row['query']);
        $this->assertEquals(1, $row['position']);
        $this->assertEquals('productName1', $row['name']);
        $this->assertEquals('productBrandName1', $row['brand_name']);
    }
}
