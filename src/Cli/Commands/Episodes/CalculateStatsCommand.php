<?php

declare(strict_types=1);

namespace App\Cli\Commands\Episodes;

use App\Cli\Services\CliQuestionService;
use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateStatsCommand extends Command
{
    private EpisodeApi $episodeApi;
    private EpisodeDownloadStatsApi $episodeDownloadStatsApi;
    private CliQuestionService $questionService;

    public function __construct(
        EpisodeApi $episodeApi,
        EpisodeDownloadStatsApi $episodeDownloadStatsApi,
        CliQuestionService $questionService
    ) {
        $this->episodeApi              = $episodeApi;
        $this->episodeDownloadStatsApi = $episodeDownloadStatsApi;
        $this->questionService         = $questionService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('episodes:calculate-stats');

        $this->setDescription(
            'Calculates stats for specified episode'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $this->questionService->ask(
            '<fg=cyan>Episode UUID: </>',
            false
        );

        // $uuid = '6ba38ac6-eef6-11ea-9c74-0242ac140003';

        $fetchModel = new FetchModel();

        $fetchModel->ids = [$uuid];

        $episode = $this->episodeApi->fetchEpisode($fetchModel);

        if ($episode === null) {
            $output->writeln(
                '<fg=red>Episode with UUID "' . $uuid . '" not found"</>'
            );

            return 1;
        }

        $this->episodeDownloadStatsApi->calculateStatsForEpisode(
            $episode
        );

        return 0;
    }
}
