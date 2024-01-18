<?php
declare(strict_types=1);

namespace App\Command;

use App\Lib\ClickHouse\WbProductsClickHouseTable;
use App\Lib\WbParser\Adapters\WbRawProductToTableRowAdapter;
use App\Lib\WbParser\WbProductParseConfig;
use App\Lib\WbParser\WbProductParser;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;

/**
 * ParseProducts command.
 */
class ParseProductsCommand extends Command
{

    public function __construct(private readonly WbProductParser $parser)
    {
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Parse wb search results and store to ClickHouse');
        $parser->addArgument('query', [
            'help' => 'Product search query',
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $query = $args->getArgument('query');
        $requestTime = DateTime::now();
        $rawProducts = $this->getRawProducts($query);

        $products = array_map(function (int $key, array $value) use ($query, $requestTime) {
            $adapter = new WbRawProductToTableRowAdapter($value);

            return array_merge([
                'query' => $query,
                'position' => $key,
                'request_date' => $requestTime->toDateTimeString()
            ], $adapter->adapt());
        },
            array_keys($rawProducts),
            array_values($rawProducts)
        );

        $this->storeProducts($products);

        return static::CODE_SUCCESS;
    }

    protected function getRawProducts($query): array
    {
        $this->parser->setParseConfig(new WbProductParseConfig(query: $query));
        return $this->parser->parse();
    }

    private function storeProducts(array $products): void
    {
        WbProductsClickHouseTable::getInstance()
            ->insert($products);
    }
}
