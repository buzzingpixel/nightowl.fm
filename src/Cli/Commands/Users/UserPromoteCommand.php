<?php

declare(strict_types=1);

namespace App\Cli\Commands\Users;

use App\Cli\Services\CliQuestionService;
use App\Context\Users\UserApi;
use App\Payload\Payload;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_walk;

class UserPromoteCommand extends Command
{
    // phpcs:disable
    protected static $defaultName = 'user:promote';
    // phpcs:enable

    private CliQuestionService $cliQuestionService;
    private UserApi $userApi;

    public function __construct(
        CliQuestionService $cliQuestionService,
        UserApi $userApi
    ) {
        $this->cliQuestionService = $cliQuestionService;
        $this->userApi            = $userApi;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userApi->fetchUserByEmailAddress(
            $this->cliQuestionService->ask(
                '<fg=cyan>User\'s Email address: </>'
            )
        );

        if ($user === null) {
            $output->writeln(
                '<fg=red>The user does not exist</>'
            );

            return 1;
        }

        $user->isAdmin = true;

        $payload = $this->userApi->saveUser($user);

        if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
            return $this->error($output, $payload);
        }

        return $this->success($output);
    }

    protected function success(OutputInterface $output): int
    {
        $output->writeln(
            '<fg=green>User was promoted to admin</>'
        );

        return 0;
    }

    protected function error(OutputInterface $output, Payload $payload): int
    {
        $output->writeln(
            '<fg=red>An error occurred</>'
        );

        $result = $payload->getResult();

        array_walk(
            $result,
            static function (
                string $message,
                string $key
            ) use (
                $output
            ): void {
                $output->writeln(
                    '<fg=red>' . $key . ': ' . $message . '</>'
                );
            }
        );

        return 1;
    }
}
