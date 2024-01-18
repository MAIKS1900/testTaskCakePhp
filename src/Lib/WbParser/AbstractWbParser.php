<?php
declare(strict_types=1);

namespace App\Lib\WbParser;

use Cake\Core\Configure;
use Cake\Http\Client;

abstract class AbstractWbParser
{
    abstract public function parse(): array;

    protected function getClient(): Client
    {
        return new Client([
            'host' => Configure::read('wbParser.host'),
            'scheme' => Configure::read('wbParser.scheme'),
        ]);
    }
}
