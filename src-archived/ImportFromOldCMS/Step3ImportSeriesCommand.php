<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Payload\Payload;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_walk;
use function file_get_contents;
use function Safe\json_decode;

class Step3ImportSeriesCommand extends Command
{
    private GuzzleClient $guzzle;
    private ShowApi $showApi;
    private SeriesApi $seriesApi;

    public function __construct(
        GuzzleClient $guzzle,
        ShowApi $showApi,
        SeriesApi $seriesApi
    ) {
        parent::__construct();

        $this->guzzle    = $guzzle;
        $this->showApi   = $showApi;
        $this->seriesApi = $seriesApi;
    }

    protected function configure(): void
    {
        $this->setName('import-from-old-cms:step-3-series');
    }

    /** @psalm-suppress PropertyNotSetInConstructor */
    private OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $output->writeln('<fg=yellow>Beginning series import...</>');

        // $response = $this->guzzle->get(
        //     implode('/', [
        //         Constants::BASE_IMPORT_URL,
        //         Constants::GET_SERIES,
        //     ]),
        //     ['verify' => false],
        // );
        // file_put_contents(
        //     '/opt/project/src/Cli/Commands/ImportFromOldCMS/step3.json',
        //     (string) $response->getBody(),
        // );
        // die;
        // /** @psalm-suppress MixedAssignment */
        // $json = json_decode((string) $response->getBody(), true);

        $json = json_decode(
            file_get_contents(
                '/opt/project/src/Cli/Commands/ImportFromOldCMS/step3.json'
            ),
            true
        );

        /** @psalm-suppress MixedArgument */
        array_walk(
            $json,
            [$this, 'processItem'],
        );

        $output->writeln('<fg=green>Series import finished!</>');

        return 0;
    }

    /**
     * @param mixed[] $data
     */
    protected function processItem(array $data): void
    {
        $title = (string) $data['title'];

        $this->output->writeln('<fg=yellow>Processing ' . $title . '...</>');

        $showFetchModel        = new ShowFetchModel();
        $showFetchModel->slugs = [(string) $data['showSlug']];

        $show = $this->showApi->fetchShow($showFetchModel);

        if ($show === null) {
            $this->output->writeln('<fg=red>Couldn\'t find show for' . $title . '!</>');

            return;
        }

        $seriesFetchModel        = new SeriesFetchModel();
        $seriesFetchModel->shows = [$show];
        $seriesFetchModel->slugs = [(string) $data['slug']];

        $existingSeries = $this->seriesApi->fetchOneSeries(
            $seriesFetchModel
        );

        if ($existingSeries !== null) {
            $this->output->writeln('<fg=red>' . $title . ' already exists in CMS!</>');

            return;
        }

        $series              = new SeriesModel();
        $series->title       = (string) $data['title'];
        $series->slug        = (string) $data['slug'];
        $series->description = (string) $data['description'];
        $series->show        = $show;

        $payload = $this->seriesApi->saveSeries($series);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            $this->output->writeln('<fg=red>' . $title . ' could not be saved!</>');

            return;
        }

        $this->output->writeln('<fg=green>' . $title . ' was saved to the CMS!</>');
    }
}
