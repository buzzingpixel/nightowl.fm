<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Migrations;

use App\Cli\Commands\Migrations\MigrateCreateCommand;
use App\Cli\Services\CliQuestionService;
use App\Utilities\CaseConversionUtility;
use Phinx\Console\PhinxApplication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestConfig;
use Throwable;

use function assert;
use function func_get_args;

class MigrateCreateCommandTest extends TestCase
{
    /** @var mixed[] */
    private array $doRunCallArgs;

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $command = new MigrateCreateCommand(
            $this->mockCliQuestionService(),
            TestConfig::$di->get(CaseConversionUtility::class),
            $this->mockPhinxApplication()
        );

        self::assertSame('migrate:create', $command->getName());

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
                0 => 'create',
                'name' => 'FooBarTestQuestion',
            ],
            $reflectionProperty->getValue($arrayInputArg)
        );

        self::assertSame($output, $this->doRunCallArgs[1]);
    }

    /**
     * @return CliQuestionService&MockObject
     */
    private function mockCliQuestionService(): CliQuestionService
    {
        $mock = $this->createMock(CliQuestionService::class);

        $mock->expects(self::once())
            ->method('ask')
            ->with(self::equalTo(
                '<fg=cyan>Provide a migration name: </>'
            ))
            ->willReturn('foo bar test question');

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
