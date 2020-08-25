<?php

declare(strict_types=1);

namespace App\Cli\Commands\Episodes;

use App\Context\Episodes\EpisodeApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishPendingEpisodesCommand extends Command
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('episodes:publish-pending');

        $this->setDescription(
            'Publishes pending episodes if past publish date'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            '<fg=yellow>Publishing pending episodes...</>'
        );

        $this->episodeApi->publishPendingEpisodes();

        $output->writeln('<fg=green>Done.</>');

        return 0;
    }
}
