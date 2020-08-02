<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Migrations;

use App\Cli\Commands\Migrations\MigrateDownCommand;
use App\Cli\Services\CliQuestionService;
use Phinx\Console\PhinxApplication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function assert;
use function func_get_args;

class MigrateDownCommandTest extends TestCase
{
    /** @var mixed[] */
    private array $doRunCallArgs;

    /**
     * @throws Throwable
     */
    public function testWithNoQuestionReturnValue(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $command = new MigrateDownCommand(
            $this->mockCliQuestionService(),
            $this->mockPhinxApplication()
        );

        self::assertSame(0, $command->execute(
            $this->createMock(InputInterface::class),
            $output
        ));

        self::assertCount(2, $this->doRunCallArgs);

        $arrayInputArg = $this->doRunCallArgs[0];
        assert($arrayInputArg instanceof ArrayInput || $arrayInputArg === null);
        self::assertInstanceOf(ArrayInput::class, $arrayInputArg);
        $reflectionClass    = new ReflectionClass(ArrayInput::class);
        $reflectionProperty = $reflectionClass->getProperty('parameters');
        $reflectionProperty->setAccessible(true);
        self::assertSame(
            [0 => 'rollback'],
            $reflectionProperty->getValue($arrayInputArg)
        );

        self::assertSame('migrate:down', $command->getName());
    }

    /**
     * @throws Throwable
     */
    public function testWithQuestionReturnValue(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $command = new MigrateDownCommand(
            $this->mockCliQuestionService('foo bar val'),
            $this->mockPhinxApplication()
        );

        self::assertSame(0, $command->execute(
            $this->createMock(InputInterface::class),
            $output
        ));

        self::assertCount(2, $this->doRunCallArgs);

        $arrayInputArg = $this->doRunCallArgs[0];
        assert($arrayInputArg instanceof ArrayInput || $arrayInputArg === null);
        self::assertInstanceOf(ArrayInput::class, $arrayInputArg);
        $reflectionClass    = new ReflectionClass(ArrayInput::class);
        $reflectionProperty = $reflectionClass->getProperty('parameters');
        $reflectionProperty->setAccessible(true);
        self::assertSame(
            [
                0 => 'rollback',
                '--target' => 'foo bar val',
            ],
            $reflectionProperty->getValue($arrayInputArg)
        );

        self::assertSame('migrate:down', $command->getName());
    }

    /**
     * @return CliQuestionService&MockObject
     */
    private function mockCliQuestionService(string $questionReturnValue = ''): CliQuestionService
    {
        $mock = $this->createMock(CliQuestionService::class);

        $mock->expects(self::once())
            ->method('ask')
            ->with(self::equalTo(
                '<fg=cyan>Specify target (0 to revert all, blank to revert last): </>'
            ))
            ->willReturn($questionReturnValue);

        return $mock;
    }

    /**
     * @return PhinxApplication&MockObject
     */
    private function mockPhinxApplication(): PhinxApplication
    {
        $this->doRunCallArgs = [];

        $mock = $this->createMock(PhinxApplication::class);

        $mock->expects(self::once())
            ->method('doRun')
            ->willReturnCallback(function (): int {
                $this->doRunCallArgs = func_get_args();

                return 0;
            });

        return $mock;
    }
}
