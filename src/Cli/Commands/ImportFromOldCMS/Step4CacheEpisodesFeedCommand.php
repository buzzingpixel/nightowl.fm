<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

use App\Context\DatabaseCache\DatabaseCacheItem;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function Safe\json_decode;

class Step4CacheEpisodesFeedCommand extends Command
{
    private GuzzleClient $guzzle;
    private CacheItemPoolInterface $cachePool;

    public function __construct(
        GuzzleClient $guzzle,
        CacheItemPoolInterface $cachePool
    ) {
        parent::__construct();

        $this->guzzle    = $guzzle;
        $this->cachePool = $cachePool;
    }

    protected function configure(): void
    {
        $this->setName('import-from-old-cms:step-4-cache-episodes-feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=yellow>Caching feed...</>');

        $response = $this->guzzle->get(
            implode('/', [
                Constants::BASE_IMPORT_URL,
                Constants::GET_EPISODES,
            ]),
            ['verify' => false],
        );

        /** @psalm-suppress MixedAssignment */
        $json = json_decode((string) $response->getBody(), true);

        $cacheItem = new DatabaseCacheItem();

        $cacheItem->key = 'OldCMSImportEpisodesFeed';

        $cacheItem->set($json);

        $cacheItem->expiresAfter(86400); // 1 day

        $this->cachePool->save($cacheItem);

        $output->writeln('<fg=green>Feed cached!</>');

        return 0;
    }
}
