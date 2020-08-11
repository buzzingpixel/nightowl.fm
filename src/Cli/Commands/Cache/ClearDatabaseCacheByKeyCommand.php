<?php

declare(strict_types=1);

namespace App\Cli\Commands\Cache;

use App\Cli\Services\CliQuestionService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearDatabaseCacheByKeyCommand extends Command
{
    private CacheItemPoolInterface $cacheItemPool;
    private CliQuestionService $cliQuestionService;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        CliQuestionService $cliQuestionService
    ) {
        $this->cacheItemPool      = $cacheItemPool;
        $this->cliQuestionService = $cliQuestionService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('cache:clear-database-by-key');

        $this->setDescription('Clears the database cache');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cacheItemPool->deleteItem($this->cliQuestionService->ask(
            '<fg=cyan>Key to delete: </>'
        ));

        $output->writeln('<fg=green>Specified cache cleared</>');

        return 0;
    }
}
