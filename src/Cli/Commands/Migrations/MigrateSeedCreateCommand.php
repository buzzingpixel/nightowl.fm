<?php

declare(strict_types=1);

namespace App\Cli\Commands\Migrations;

use App\Cli\Services\CliQuestionService;
use App\Utilities\CaseConversionUtility;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSeedCreateCommand extends Command
{
    // phpcs:disable
    protected static $defaultName = 'migrate:create-seed';
    // phpcs:enable

    private CliQuestionService $cliQuestionService;
    private CaseConversionUtility $caseConversionUtility;
    private PhinxApplication $phinxApplication;

    public function __construct(
        CliQuestionService $cliQuestionService,
        CaseConversionUtility $caseConversionUtility,
        PhinxApplication $phinxApplication
    ) {
        $this->cliQuestionService    = $cliQuestionService;
        $this->caseConversionUtility = $caseConversionUtility;
        $this->phinxApplication      = $phinxApplication;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->phinxApplication->doRun(
            new ArrayInput([
                0 => 'seed:create',
                'name' => $this->caseConversionUtility->convertStringToPascale(
                    $this->cliQuestionService->ask(
                        '<fg=cyan>Provide a seed name: </>'
                    )
                ),
            ]),
            $output
        );
    }
}
