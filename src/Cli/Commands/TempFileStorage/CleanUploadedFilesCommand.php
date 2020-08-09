<?php

declare(strict_types=1);

namespace App\Cli\Commands\TempFileStorage;

use App\Context\TempFileStorage\TempFileStorageApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUploadedFilesCommand extends Command
{
    private TempFileStorageApi $tempFileStorageApi;

    public function __construct(TempFileStorageApi $tempFileStorageApi)
    {
        $this->tempFileStorageApi = $tempFileStorageApi;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('temp-file-storage:clean-uploaded-files');

        $this->setDescription('Cleans expired uploaded temp files');
    }

    protected function execute(InputInterface $in, OutputInterface $out): int
    {
        $out->writeln('<fg=yellow>Cleaning uploaded temp files...</>');

        $this->tempFileStorageApi->cleanUploadedFiles();

        $out->writeln('<fg=green>Uploaded temp files clean</>');

        return 0;
    }
}
