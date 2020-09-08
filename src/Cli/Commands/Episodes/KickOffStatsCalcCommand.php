<?php

declare(strict_types=1);

namespace App\Cli\Commands\Episodes;

use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KickOffStatsCalcCommand extends Command
{
    private EpisodeDownloadStatsApi $episodeDownloadStatsApi;

    public function __construct(
        EpisodeDownloadStatsApi $episodeDownloadStatsApi
    ) {
        $this->episodeDownloadStatsApi = $episodeDownloadStatsApi;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('episodes:kick-off-stats-calc');

        $this->setDescription(
            'Begins queue process for calculating stats on all episodes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->episodeDownloadStatsApi->kickOffCalculateDownloadStats();

        return 0;
    }
}
