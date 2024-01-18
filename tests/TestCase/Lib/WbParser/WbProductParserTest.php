<?php
declare(strict_types=1);

namespace App\Test\TestCase\Lib\WbParser;

use App\Lib\WbParser\WbProductParseConfig;
use App\Lib\WbParser\WbProductParser;
use Cake\Core\Configure;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;

class WbProductParserTest extends TestCase
{
    use HttpClientTrait;

    public function testWbSearchWorks()
    {
        $parser = new WbProductParser();
        $parser->setParseConfig(new WbProductParseConfig(query: 'гель для мытья посуды', limit: 1));
        $result = $parser->parse();

        $this->assertNotEmpty($result);
    }

    public function testNoConnectionEmptyResult()
    {
        $this->mockClientGet($this->makeUrlPath('query', 1),
            $this->newClientResponse(400)
        );

        $parser = new WbProductParser();
        $parser->setParseConfig(new WbProductParseConfig(query: 'query', limit: 1));
        $result = $parser->parse();

        $this->assertEmpty($result);
    }

    public function testLimitResult()
    {
        $body = $this->makeBody('query', array_fill(0, 100, [
            'name' => 'name',
            'brand' => 'brand'
        ]));
        $this->mockClientGet($this->makeUrlPath('query', 1), $this->newClientResponse(200, [], $body));
        $this->mockClientGet($this->makeUrlPath('query', 2), $this->newClientResponse(200, [], $body));

        $parser = new WbProductParser();
        $parser->setParseConfig(new WbProductParseConfig(query: 'query', limit: 150));
        $result = $parser->parse();

        $this->assertEquals(150, count($result));

        $body = $this->makeBody('query', array_fill(0, 100, [
            'name' => 'name',
            'brand' => 'brand'
        ]));
        $this->mockClientGet($this->makeUrlPath('query', 1), $this->newClientResponse(200, [], $body));
        $this->mockClientGet($this->makeUrlPath('query', 2), $this->newClientResponse(200, [], $body));
        $this->mockClientGet($this->makeUrlPath('query', 3), $this->newClientResponse(200, [], $body = $this->makeBody('query', [])));

        $parser = new WbProductParser();
        $parser->setParseConfig(new WbProductParseConfig(query: 'query', limit: 300));
        $result = $parser->parse();

        $this->assertEquals(200, count($result));
    }

    private function makeUrlPath(string $query, int $page): string
    {
        return Configure::read('wbParser.scheme') . '://' . Configure::read('wbParser.host') . Configure::read('wbParser.paths.product')
            . '?' . http_build_query(array_merge(WbProductParser::DEFAULT_REQUEST_PARAMS, [
                'query' => $query,
                'page' => $page
            ]), "", null, PHP_QUERY_RFC3986);
    }

    private function makeBody(string $query, $products): string
    {
        $body = [
            "metadata" => [
                "normquery" => $query,
            ],
            "state" => 0,
            "data" => [
                "products" => $products
            ]
        ];
        if (count($products) > 0) {
            $body['metadata']['name'] = $query;
        }
        return json_encode($body);
    }
}
