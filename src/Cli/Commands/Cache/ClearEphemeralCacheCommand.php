<?php

declare(strict_types=1);

namespace App\Cli\Commands\Cache;

use App\Http\ServiceSuites\StaticCache\StaticCacheApi;
use App\Http\ServiceSuites\TwigCache\TwigCacheApi;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearEphemeralCacheCommand extends Command
{
    private StaticCacheApi $staticCacheApi;
    private TwigCacheApi $twigCacheApi;

    public function __construct(
        StaticCacheApi $staticCacheApi,
        TwigCacheApi $twigCacheApi
    ) {
        parent::__construct();

        $this->staticCacheApi = $staticCacheApi;
        $this->twigCacheApi   = $twigCacheApi;
    }

    protected function configure(): void
    {
        $this->setName('cache:clear-ephemeral');

        $this->setDescription('Clears static and twig caches');
    }

    /**
     * @throws ReflectionException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
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
