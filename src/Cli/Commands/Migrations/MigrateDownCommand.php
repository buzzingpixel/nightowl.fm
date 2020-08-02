<?php

declare(strict_types=1);

namespace App\Cli\Commands\Migrations;

use App\Cli\Services\CliQuestionService;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDownCommand extends Command
{
    // phpcs:disable
    protected static $defaultName = 'migrate:down';
    // phpcs:enable

    private CliQuestionService $cliQuestionService;
    private PhinxApplication $phinxApplication;

    public function __construct(
        CliQuestionService $cliQuestionService,
        PhinxApplication $phinxApplication
    ) {
        $this->cliQuestionService = $cliQuestionService;
        $this->phinxApplication   = $phinxApplication;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $params = ['rollback'];

        $target = $this->cliQuestionService->ask(
            '<fg=cyan>Specify target (0 to revert all, blank to revert last): </>',
            false
        );

        if ($target !== '') {
            $params['--target'] = $target;
        }

        return $this->phinxApplication->doRun(
            new ArrayInput($params),
            $output
        );
    }
}
