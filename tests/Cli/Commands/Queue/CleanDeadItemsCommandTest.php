<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Queue;

use App\Cli\Commands\Queue\CleanDeadItemsCommand;
use App\Context\Queue\QueueApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanDeadItemsCommandTest extends TestCase
{
    public function testConfiguration(): void
    {
        $cmd = new CleanDeadItemsCommand(
            $this->createMock(QueueApi::class),
        );

        self::assertSame(
            'queue:clean-dead-items',
            $cmd->getName()
        );

        self::assertSame(
            'Cleans dead queue items',
            $cmd->getDescription(),
        );
    }

    public function testWhenNoItems(): void
    {
        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('cleanDeadItems')
            ->willReturn(0);

        $cmd = new CleanDeadItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning dead items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>There were no dead items</>'
            ));

        self::assertSame(0, $cmd->execute(
            $this->createMock(InputInterface::class),
            $output
        ));
    }

    public function testWhenOneCleaned(): void
    {
        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('cleanDeadItems')
            ->willReturn(1);

        $cmd = new CleanDeadItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning dead items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>1 dead item was cleaned</>'
            ));

        self::assertSame(0, $cmd->execute(
            $this->createMock(InputInterface::class),
            $output
        ));
    }

    public function testWhenTwoCleaned(): void
    {
        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('cleanDeadItems')
            ->willReturn(2);

        $cmd = new CleanDeadItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning dead items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>2 dead items were cleaned</>'
            ));

        self::assertSame(0, $cmd->execute(
            $this->createMock(InputInterface::class),
            $output
        ));
    }

    public function test(): void
    {
        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('cleanDeadItems')
            ->willReturn(42);

        $cmd = new CleanDeadItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning dead items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>42 dead items were cleaned</>'
            ));

        self::assertSame(0, $cmd->execute(
            $this->createMock(InputInterface::class),
            $output
        ));
    }
}
