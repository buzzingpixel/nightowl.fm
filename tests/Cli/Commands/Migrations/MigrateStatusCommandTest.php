<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Migrations;

use App\Cli\Commands\Migrations\MigrateStatusCommand;
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

class MigrateStatusCommandTest extends TestCase
{
    /** @var mixed[] */
    private array $doRunCallArgs;

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $command = new MigrateStatusCommand(
            $this->mockPhinxApplication()
        );

        self::assertSame('migrate:status', $command->getName());

        self::assertSame(0, $command->execute(
            $this->createMock(InputInterface::class),
            $output
        ));

        $arrayInputArg = $this->doRunCallArgs[0];
        assert($arrayInputArg instanceof ArrayInput || $arrayInputArg === null);
        self::assertInstanceOf(ArrayInput::class, $arrayInputArg);
        $reflectionClass    = new ReflectionClass(ArrayInput::class);
        $reflectionProperty = $reflectionClass->getProperty('parameters');
        $reflectionProperty->setAccessible(true);
        self::assertSame(
            ['status'],
            $reflectionProperty->getValue($arrayInputArg)
        );

        self::assertSame($output, $this->doRunCallArgs[1]);

        self::assertCount(2, $this->doRunCallArgs);
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
