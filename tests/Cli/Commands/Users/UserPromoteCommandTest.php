<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Users;

use App\Cli\Commands\Users\UserPromoteCommand;
use App\Cli\Services\CliQuestionService;
use App\Context\Users\Models\UserModel;
use App\Context\Users\UserApi;
use App\Payload\Payload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function func_get_args;

class UserPromoteCommandTest extends TestCase
{
    private UserPromoteCommand $command;

    private ?Payload $payload = null;

    private ?UserModel $userModel = null;

    /** @var mixed[] */
    private array $saveUserCallArgs;

    public function testWhenNoMatchedUser(): void
    {
        $this->internalSetup();

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(
                self::equalTo('<fg=red>The user does not exist</>')
            );

        $return = $this->command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(1, $return);
    }

    public function testWhenPayloadStatusIsNotUpdated(): void
    {
        $this->payload = new Payload(Payload::STATUS_NOT_UPDATED, [
            'foo' => 'bar',
            'baz' => 'foo',
        ]);

        $this->userModel          = new UserModel();
        $this->userModel->isAdmin = false;

        $this->internalSetup();

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(
                self::equalTo('<fg=red>An error occurred</>')
            );

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(
                self::equalTo('<fg=red>foo: bar</>')
            );

        $output->expects(self::at(2))
            ->method('writeln')
            ->with(
                self::equalTo('<fg=red>baz: foo</>')
            );

        $return = $this->command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(1, $return);

        self::assertCount(1, $this->saveUserCallArgs);

        $userModel = $this->saveUserCallArgs[0];
        assert($userModel instanceof UserModel || $userModel === null);

        self::assertSame($this->userModel, $userModel);

        /** @phpstan-ignore-next-line */
        self::assertTrue($userModel->isAdmin);
    }

    public function testWhenPayloadStatusIsUpdated(): void
    {
        $this->payload = new Payload(Payload::STATUS_UPDATED);

        $this->userModel          = new UserModel();
        $this->userModel->isAdmin = false;

        $this->internalSetup();

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(
                self::equalTo('<fg=green>User was promoted to admin</>')
            );

        $return = $this->command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);

        self::assertCount(1, $this->saveUserCallArgs);

        $userModel = $this->saveUserCallArgs[0];
        assert($userModel instanceof UserModel || $userModel === null);

        self::assertSame($this->userModel, $userModel);

        /** @phpstan-ignore-next-line */
        self::assertTrue($userModel->isAdmin);
    }

    private function internalSetup(): void
    {
        $this->saveUserCallArgs = [];

        $this->command = new UserPromoteCommand(
            $this->mockQuestionService(),
            $this->mockUserApi()
        );

        self::assertSame('user:promote', $this->command->getName());
    }

    /**
     * @return CliQuestionService&MockObject
     */
    private function mockQuestionService(): CliQuestionService
    {
        $mock = $this->createMock(CliQuestionService::class);

        $mock->expects(self::once())
            ->method('ask')
            ->with(
                self::equalTo('<fg=cyan>User\'s Email address: </>')
            )
            ->willReturn('foo@bar.baz');

        return $mock;
    }

    /**
     * @return UserApi&MockObject
     */
    private function mockUserApi(): UserApi
    {
        $mock = $this->createMock(UserApi::class);

        $mock->method('saveUser')
            ->willReturnCallback(function (): ?Payload {
                $this->saveUserCallArgs = func_get_args();

                return $this->payload;
            });

        $mock->expects(self::once())
            ->method('fetchUserByEmailAddress')
            ->with(self::equalTo('foo@bar.baz'))
            ->willReturn($this->userModel);

        return $mock;
    }
}
