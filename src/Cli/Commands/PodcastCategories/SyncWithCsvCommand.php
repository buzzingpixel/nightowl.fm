<?php

declare(strict_types=1);

namespace App\Cli\Commands\PodcastCategories;

use App\Context\PodcastCategories\Services\SyncWithCsv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncWithCsvCommand extends Command
{
    private SyncWithCsv $syncWithCsv;

    public function __construct(SyncWithCsv $syncWithCsv)
    {
        $this->syncWithCsv = $syncWithCsv;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('categories:sync-with-csv');

        $this->setDescription(
            'Syncs categories with the CSV on disk'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            '<fg=yellow>Syncing...</>'
        );

        $this->syncWithCsv->sync();

        $output->writeln('<fg=green>Done.</>');

        return 0;
    }
}
