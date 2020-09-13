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
use function file_get_contents;
use function file_put_contents;
use function is_array;
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

    /** @psalm-suppress PropertyNotSetInConstructor */
    private OutputInterface $output;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $output->writeln('<fg=yellow>Beginning people import...</>');

        // $response = $this->guzzle->get(
        //     implode('/', [
        //         Constants::BASE_IMPORT_URL,
        //         Constants::GET_USERS,
        //     ]),
        //     ['verify' => false],
        // );
        // file_put_contents(
        //     '/opt/project/src/Cli/Commands/ImportFromOldCMS/step1.json',
        //     (string) $response->getBody(),
        // );
        // die;
        // /** @psalm-suppress MixedAssignment */
        // $json = json_decode((string) $response->getBody(), true);

        $json = json_decode(
            file_get_contents(
                '/opt/project/src/Cli/Commands/ImportFromOldCMS/step1.json'
            ),
            true
        );

        /** @psalm-suppress MixedArgument */
        array_walk(
            $json,
            [$this, 'processItem'],
        );

        $output->writeln('<fg=green>People import finished!</>');

        return 0;
    }

    /**
     * @param mixed[] $data
     */
    protected function processItem(array $data): void
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

        /** @psalm-suppress MixedAssignment */
        foreach ($data['links'] as $link) {
            $link = is_array($link) ? $link : [];

            $links[] = new LinkModel(
                (string) $link['linkTitle'],
                (string) $link['linkUrl'],
            );
        }

        $person                   = new PersonModel();
        $person->firstName        = (string) $data['firstName'];
        $person->lastName         = (string) $data['lastName'];
        $person->slug             = (string) $data['slug'];
        $person->email            = (string) $data['email'];
        $person->photoPreference  = $data['photoPreference'] === 'gravatar' ? 'gravatar' : 'cms';
        $person->bio              = (string) $data['bio'];
        $person->location         = (string) $data['location'];
        $person->facebookPageSlug = (string) $data['facebookPageSlug'];
        $person->twitterHandle    = (string) $data['twitterHandle'];
        $person->setLinks($links);

        try {
            if ($data['photo'] === '') {
                throw new Exception();
            }

            /** @phpstan-ignore-next-line */
            $url = (string) preg_replace(
                '/\?.*/',
                '',
                (string) $data['photo']
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
    }
}
