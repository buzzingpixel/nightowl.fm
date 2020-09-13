<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

use App\Context\People\Models\FetchModel as PeopelFetchModel;
use App\Context\People\PeopleApi;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\PodcastCategoriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Payload\Payload;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use App\Utilities\SystemClock;
use Cocur\Slugify\Slugify;
use Config\General;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function array_walk;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function pathinfo;
use function preg_replace;
use function Safe\json_decode;

class Step2ImportShowsCommand extends Command
{
    private GuzzleClient $guzzle;
    private PeopleApi $peopleApi;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private General $generalConfig;
    private Filesystem $filesystem;
    private Slugify $slugify;
    private SystemClock $clock;
    private ShowApi $showApi;
    private PodcastCategoriesApi $podcastCategoriesApi;
    /** @var PodcastCategoryModel[] */
    private array $allCategories;

    public function __construct(
        GuzzleClient $guzzle,
        PeopleApi $peopleApi,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        General $generalConfig,
        Filesystem $filesystem,
        Slugify $slugify,
        SystemClock $clock,
        ShowApi $showApi,
        PodcastCategoriesApi $podcastCategoriesApi
    ) {
        parent::__construct();

        $this->guzzle               = $guzzle;
        $this->peopleApi            = $peopleApi;
        $this->uuidFactory          = $uuidFactory;
        $this->generalConfig        = $generalConfig;
        $this->filesystem           = $filesystem;
        $this->slugify              = $slugify;
        $this->clock                = $clock;
        $this->showApi              = $showApi;
        $this->podcastCategoriesApi = $podcastCategoriesApi;

        $this->allCategories = $this->podcastCategoriesApi->fetchCategories();
    }

    protected function configure(): void
    {
        $this->setName('import-from-old-cms:step-2-shows');
    }

    /** @psalm-suppress PropertyNotSetInConstructor */
    private OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $output->writeln('<fg=yellow>Beginning show import...</>');

        // $response = $this->guzzle->get(
        //     implode('/', [
        //         Constants::BASE_IMPORT_URL,
        //         Constants::GET_SHOWS,
        //     ]),
        //     ['verify' => false],
        // );
        // file_put_contents(
        //     '/opt/project/src/Cli/Commands/ImportFromOldCMS/step2.json',
        //     (string) $response->getBody(),
        // );
        // die;
        // /** @psalm-suppress MixedAssignment */
        // $json = json_decode((string) $response->getBody(), true);

        $json = json_decode(
            file_get_contents(
                '/opt/project/src/Cli/Commands/ImportFromOldCMS/step2.json'
            ),
            true
        );

        /** @psalm-suppress MixedArgument */
        array_walk(
            $json,
            [$this, 'processItem'],
        );

        $output->writeln('<fg=green>Show import finished!</>');

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
        $showFetchModel->slugs = [(string) $data['slug']];

        $existingShow = $this->showApi->fetchShow($showFetchModel);

        if ($existingShow !== null) {
            $this->output->writeln('<fg=red>' . $title . ' already exists in CMS!</>');

            return;
        }

        $hostsFetchModel = new PeopelFetchModel();
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         * @psalm-suppress MixedAssignment
         */
        $hostsFetchModel->slugs = $data['hosts'];

        $hosts = $this->peopleApi->fetchPeople($hostsFetchModel);

        $categories = [];

        foreach ($this->allCategories as $category) {
            if (
                ! in_array(
                    $category->getParentChainWithSelfAsPath(),
                    (array) $data['categories'],
                    true,
                )
            ) {
                continue;
            }

            $categories[] = $category;
        }

        $model                    = new ShowModel();
        $model->title             = (string) $data['title'];
        $model->slug              = (string) $data['slug'];
        $model->description       = (string) $data['description'];
        $model->status            = (string) $data['showStatus'];
        $model->explicit          = (bool) $data['explicit'];
        $model->itunesLink        = (string) $data['itunesLink'];
        $model->googlePlayLink    = (string) $data['googlePlayLink'];
        $model->stitcherLink      = (string) $data['stitcherLink'];
        $model->hosts             = $hosts;
        $model->podcastCategories = $categories;

        $model->setKeywordsFromCommaString(
            implode(', ', (array) $data['keywords'])
        );

        try {
            if ($data['showArt'] === '') {
                throw new Exception();
            }

            /** @phpstan-ignore-next-line */
            $url = (string) preg_replace(
                '/\?.*/',
                '',
                (string) $data['showArt']
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

            $filePath = $directory . '/' . $fileName;

            $response = $this->guzzle->get(
                $url,
                ['verify' => false],
            );

            /** @phpstan-ignore-next-line */
            file_put_contents(
                $filePath,
                (string) $response->getBody(),
            );

            if (file_exists($filePath)) {
                $model->newArtworkFileLocation = $timeStamp . '/' . $uuid . '/' . $fileName;
            }
        } catch (Throwable $e) {
        }

        $payload = $this->showApi->saveShow($model);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            $this->output->writeln('<fg=red>' . $title . ' could not be saved!</>');

            return;
        }

        $this->output->writeln('<fg=green>' . $title . ' was saved to the CMS!</>');
    }
}
