<?php

declare(strict_types=1);

namespace App\Cli\Commands\Users;

use App\Cli\Services\CliQuestionService;
use App\Context\Users\Models\UserModel;
use App\Context\Users\UserApi;
use App\Payload\Payload;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_walk;

class UserCreateCommand extends Command
{
    // phpcs:disable
    protected static $defaultName = 'user:create';
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
        $emailAddress = $this->cliQuestionService->ask(
            '<fg=cyan>Email address: </>'
        );

        $password = $this->cliQuestionService->ask(
            '<fg=cyan>Password: </>',
            true,
            true
        );

        $userModel               = new UserModel();
        $userModel->emailAddress = $emailAddress;
        $userModel->newPassword  = $password;

        $payload = $this->userApi->saveUser($userModel);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            return $this->error($output, $payload);
        }

        return $this->success($output, $payload);
    }

    protected function success(OutputInterface $output, Payload $payload): int
    {
        /** @var array<string, string> $result */
        $result = $payload->getResult();

        $output->writeln(
            '<fg=green>' . $result['message'] . '</>'
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
