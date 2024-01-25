<?php
declare(strict_types=1);

namespace App\Lib\WbParser;

use Cake\Core\Configure;
use Cake\Log\Log;

class WbProductParser extends AbstractWbParser
{
    public const DEFAULT_REQUEST_PARAMS = [
        'TestGroup' => 'no_test',
        'TestID' => 'no_test',
        'appType' => 1,
        'curr' => 'rub',
        'dest' => -1257786,
        'resultset' => 'catalog',
        'sort' => 'popular',
        'spp' => '1',
        'suppressSpellcheck' => false,
    ];

    private WbProductParseConfig $parseConfig;

    public function __construct()
    {
    }

    /**
     * @param WbProductParseConfig $parseConfig
     * @return $this
     */
    public function setParseConfig(WbProductParseConfig $parseConfig): static
    {
        $this->parseConfig = $parseConfig;
        return $this;
    }

    /**
     * @return array[]
     */
    public function parse(): array
    {
        $stack = [];
        $page = 1;
        while (count($stack) < $this->parseConfig->limit) {
            $products = $this->parsePage($page++);
            if (count($products) === 0) {
                break;
            }

            $stack = array_merge($stack, $products);
        }

        array_splice($stack, $this->parseConfig->limit);
        return $stack;
    }

    /**
     * @param int $page
     * @return array
     */
    private function parsePage(int $page): array
    {
        $response = $this->getClient()->get(
            Configure::read('wbParser.paths.product'),
            array_merge(self::DEFAULT_REQUEST_PARAMS, [
                'query' => $this->parseConfig->query,
                'page' => $page
            ]));

        if (!$response->isSuccess()) {
            Log::error('WbProductParser. Failed request: ' . $response->getStringBody());
            return [];
        }
        $body = $response->getJson();

        return array_key_exists('data', $body) && array_key_exists('products', $body['data']) ? $body['data']['products'] : [];
    }
}
