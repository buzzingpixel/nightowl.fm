<?php

declare(strict_types=1);

namespace App\Cli\Commands\Cache;

use App\Http\ServiceSuites\StaticCache\StaticCacheApi;
use App\Http\ServiceSuites\TwigCache\TwigCacheApi;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAllCacheCommand extends Command
{
    private StaticCacheApi $staticCacheApi;
    private CacheItemPoolInterface $cacheItemPool;
    private TwigCacheApi $twigCacheApi;

    public function __construct(
        StaticCacheApi $staticCacheApi,
        CacheItemPoolInterface $cacheItemPool,
        TwigCacheApi $twigCacheApi
    ) {
        parent::__construct();

        $this->staticCacheApi = $staticCacheApi;
        $this->cacheItemPool  = $cacheItemPool;
        $this->twigCacheApi   = $twigCacheApi;
    }

    protected function configure(): void
    {
        $this->setName('cache:clear');

        $this->setDescription('Clears all cache types');
    }

    /**
     * @throws ReflectionException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=yellow>Clearing database cache...</>');

        $this->cacheItemPool->clear();

        $output->writeln('<fg=green>Database cache cleared</>');

        $output->writeln('<fg=yellow>Clearing twig cache...</>');

        if ($this->twigCacheApi->clearTwigCache()) {
            $output->writeln('<fg=green>Twig cache cleared</>');
        } else {
            $output->writeln('<fg=green>Twig cache not enabled</>');
        }

        $output->writeln('<fg=yellow>Clearing static cache...</>');

        $this->staticCacheApi->clearStaticCache();

        $output->writeln('<fg=green>Static cache cleared</>');

        return 0;
    }
}
