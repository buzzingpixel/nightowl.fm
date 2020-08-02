<?php

declare(strict_types=1);

namespace App\Cli\Commands\Migrations;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateUpCommand extends Command
{
    // phpcs:disable
    protected static $defaultName = 'migrate:up';
    // phpcs:enable

    private PhinxApplication $phinxApplication;

    public function __construct(PhinxApplication $phinxApplication)
    {
        $this->phinxApplication = $phinxApplication;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->phinxApplication->doRun(
            new ArrayInput(['migrate']),
            $output
        );
    }
}
