<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel as EpiosdeFetchModel;
use App\Context\People\Models\FetchModel as PeopelFetchModel;
use App\Context\People\PeopleApi;
use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Payload\Payload;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use App\Utilities\SystemClock;
use Cocur\Slugify\Slugify;
use Config\General;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use League\Flysystem\Filesystem;
use Psr\Cache\CacheItemPoolInterface;
use Safe\DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function count;
use function file_exists;
use function file_put_contents;
use function implode;
use function pathinfo;
use function preg_replace;

class Step5ImportEpisodesCommand extends Command
{
    private CacheItemPoolInterface $cachePool;
    private GuzzleClient $guzzle;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;
    private PeopleApi $peopleApi;
    private SeriesApi $seriesApi;
    private SystemClock $clock;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private General $generalConfig;
    private Filesystem $filesystem;
    private Slugify $slugify;

    public function __construct(
        CacheItemPoolInterface $cachePool,
        GuzzleClient $guzzle,
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        PeopleApi $peopleApi,
        SeriesApi $seriesApi,
        SystemClock $clock,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        General $generalConfig,
        Filesystem $filesystem,
        Slugify $slugify
    ) {
        parent::__construct();

        $this->cachePool     = $cachePool;
        $this->guzzle        = $guzzle;
        $this->showApi       = $showApi;
        $this->episodeApi    = $episodeApi;
        $this->peopleApi     = $peopleApi;
        $this->seriesApi     = $seriesApi;
        $this->clock         = $clock;
        $this->uuidFactory   = $uuidFactory;
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->slugify       = $slugify;
    }

    protected function configure(): void
    {
        $this->setName('import-from-old-cms:step-5-episodes');
    }

    /** @psalm-suppress PropertyNotSetInConstructor */
    private OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $output->writeln('<fg=yellow>Beginning episode import...</>');

        $cachedFeedItem = $this->cachePool->getItem('OldCMSImportEpisodesFeed');

        /** @var mixed[] $feed */
        $feed = $cachedFeedItem->get();

        /** @psalm-suppress MixedAssignment */
        foreach ($feed as $slug => $episodes) {
            /**
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedArgumentTypeCoercion
             */
            $this->processShow($slug, $episodes);
        }

        $output->writeln('<fg=green>Episode import finished!</>');

        return 0;
    }

    /**
     * @param mixed[] $episodes
     */
    protected function processShow(string $slug, array $episodes): void
    {
        $this->output->writeln('<fg=yellow>Processing show ' . $slug . '...</>');

        $showFetchModel        = new ShowFetchModel();
        $showFetchModel->slugs = [$slug];

        $show = $this->showApi->fetchShow($showFetchModel);

        if ($show === null) {
            $this->output->writeln('<fg=red>Couldn\'t find show for' . $slug . '!</>');

            return;
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($episodes as $episode) {
            /** @psalm-suppress MixedArgument */
            $this->processEpisode($show, $episode);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function processEpisode(ShowModel $show, array $data): void
    {
        $title = (string) $data['title'];

        $this->output->writeln('<fg=yellow>Processing ' . $title . '...</>');

        $episodeFetchModel                 = new EpiosdeFetchModel();
        $episodeFetchModel->shows          = [$show];
        $episodeFetchModel->episodeNumbers = [(int) $data['episodeNumber']];

        $existingEpisode = $this->episodeApi->fetchEpisode(
            $episodeFetchModel
        );

        if ($existingEpisode !== null) {
            $this->output->writeln('<fg=red>' . $title . ' already exists in CMS!</>');

            return;
        }

        $publishedAt = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            (string) $data['publishedAt'],
        )->setTimezone(new DateTimeZone('UTC'));

        $hostsFetchModel = new PeopelFetchModel();
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         * @psalm-suppress MixedAssignment
         */
        $hostsFetchModel->slugs = $data['hosts'];

        $hosts = $this->peopleApi->fetchPeople($hostsFetchModel);

        $guestsFetchModel = new PeopelFetchModel();
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         * @psalm-suppress MixedAssignment
         */
        $guestsFetchModel->slugs = (array) $data['guests'];

        $guests = [];

        if (count($guestsFetchModel->slugs) > 0) {
            $guests = $this->peopleApi->fetchPeople($guestsFetchModel);
        }

        $seriesFetchModel        = new SeriesFetchModel();
        $seriesFetchModel->shows = [$show];
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         * @psalm-suppress MixedAssignment
         */
        $seriesFetchModel->slugs = (array) $data['series'];

        $series = [];

        if (count($seriesFetchModel->slugs) > 0) {
            $series = $this->seriesApi->fetchSeries($seriesFetchModel);
        }

        $episode               = new EpisodeModel();
        $episode->show         = $show;
        $episode->title        = (string) $data['title'];
        $episode->number       = (int) $data['episodeNumber'];
        $episode->displayOrder = (int) $data['episodeNumber'];
        $episode->status       = EpisodeConstants::EPISODE_STATUS_LIVE;
        $episode->isPublished  = true;
        $episode->description  = (string) $data['description'];
        $episode->episodeType  = EpisodeConstants::EPISODE_TYPE_NUMBERED;
        $episode->explicit     = (bool) $data['explicit'];
        $episode->showNotes    = (string) $data['showNotes'];
        $episode->oldGuid      = (string) $data['legacyGuid'];
        $episode->publishedAt  = $publishedAt;
        $episode->hosts        = $hosts;
        $episode->guests       = $guests;
        $episode->series       = $series;

        $episode->setKeywordsFromCommaString(
            implode(', ', (array) $data['keywords'])
        );

        // $episode->newFileLocation = $data['file_path'];

        try {
            if ($data['playableEpisodeFile'] === '') {
                throw new Exception('Playable episode file download not found');
            }

            /** @phpstan-ignore-next-line */
            $url = (string) preg_replace(
                '/\?.*/',
                '',
                (string) $data['playableEpisodeFile']
            );

            $timeStamp = $this->clock->getCurrentTime()->getTimestamp();

            $uuid = $this->uuidFactory->uuid1()->toString();

            $directory = $this->generalConfig->pathToStorageDirectory();

            $directory .= '/temp/' . $timeStamp . '/' . $uuid;

            $this->filesystem->createDir($directory);

            $pathInfo = pathinfo($url);

            $fileName = $this->slugify->slugify($pathInfo['filename']);

            if (($pathInfo['extension'] ?? '') !== '') {
                /** @phpstan-ignore-next-line */
                $fileName .= '.' . (string) ($pathInfo['extension'] ?? '');
            }

            $response = $this->guzzle->get(
                $url,
                ['verify' => false],
            );

            $filePath = $directory . '/' . $fileName;

            /** @phpstan-ignore-next-line */
            file_put_contents(
                $filePath,
                (string) $response->getBody(),
            );

            if (file_exists($filePath)) {
                $episode->newFileLocation = $timeStamp . '/' . $uuid . '/' . $fileName;
            }
        } catch (Throwable $e) {
            throw $e;
        }

        $payload = $this->episodeApi->saveEpisode($episode);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            throw new Exception('There was an error creating the episode ' . $title);
        }

        $this->output->writeln('<fg=green>' . $title . ' was saved to the CMS!</>');
    }
}
