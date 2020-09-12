<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

use App\Context\Links\Models\LinkModel;
use App\Context\People\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
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
use function file_put_contents;
use function implode;
use function pathinfo;
use function preg_replace;
use function Safe\json_decode;

class Step1ImporterPeopleCommand extends Command
{
    private GuzzleClient $guzzle;
    private PeopleApi $peopleApi;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private General $generalConfig;
    private Filesystem $filesystem;
    private Slugify $slugify;
    private SystemClock $clock;

    public function __construct(
        GuzzleClient $guzzle,
        PeopleApi $peopleApi,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        General $generalConfig,
        Filesystem $filesystem,
        Slugify $slugify,
        SystemClock $clock
    ) {
        parent::__construct();

        $this->guzzle        = $guzzle;
        $this->peopleApi     = $peopleApi;
        $this->uuidFactory   = $uuidFactory;
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->slugify       = $slugify;
        $this->clock         = $clock;
    }

    protected function configure(): void
    {
        $this->setName('import-from-old-cms:step-1-people');
    }

    private OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $output->writeln('<fg=yellow>Beginning import...</>');

        $response = $this->guzzle->get(
            implode('/', [
                Constants::BASE_IMPORT_URL,
                Constants::GET_USERS,
            ]),
            ['verify' => false],
        );

        $json = json_decode((string) $response->getBody(), true);

        array_walk(
            $json,
            [$this, 'processPerson'],
        );

        $output->writeln('<fg=green>Import finished!</>');

        return 0;
    }

    /**
     * @param mixed[] $data
     */
    protected function processPerson(array $data): void
    {
        $name = ((string) $data['firstName']) . ' ' . ((string) $data['lastName']);

        $this->output->writeln('<fg=yellow>Processing ' . $name . '...</>');

        $fetchModel = new FetchModel();

        $fetchModel->slugs = [(string) $data['slug']];

        $existingPerson = $this->peopleApi->fetchPerson($fetchModel);

        if ($existingPerson !== null) {
            $this->output->writeln('<fg=red>' . $name . ' already exists in CMS!</>');

            return;
        }

        $links = [];

        foreach ($data['links'] as $link) {
            $links[] = new LinkModel(
                $link['linkTitle'],
                $link['linkUrl'],
            );
        }

        $person            = new PersonModel();
        $person->firstName = $data['firstName'];
        $person->lastName  = $data['lastName'];
        $person->slug      = $data['slug'];
        $person->email     = $data['email'];
        // $person->newPhotoFileLocation = $data['photo_file_path'];
        $person->photoPreference  = $data['photoPreference'] === 'gravatar' ? 'gravatar' : 'cms';
        $person->bio              = $data['bio'];
        $person->location         = $data['location'];
        $person->facebookPageSlug = $data['facebookPageSlug'];
        $person->twitterHandle    = $data['twitterHandle'];
        $person->setLinks($links);

        try {
            if ($data['photo'] === '') {
                throw new Exception();
            }

            $url = (string) preg_replace(
                '/\?.*/',
                '',
                $data['photo']
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

            file_put_contents(
                $filePath,
                (string) $response->getBody(),
            );

            if (file_exists($filePath)) {
                $person->newPhotoFileLocation = $timeStamp . '/' . $uuid . '/' . $fileName;
            }
        } catch (Throwable $e) {
        }

        $payload = $this->peopleApi->savePerson($person);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            $this->output->writeln('<fg=red>' . $name . ' could not be saved!</>');

            return;
        }

        $this->output->writeln('<fg=green>' . $name . ' was saved to the CMS!</>');

        // sleep(1);
    }
}
