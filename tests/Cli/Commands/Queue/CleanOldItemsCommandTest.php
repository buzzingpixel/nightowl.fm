<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Queue;

use App\Cli\Commands\Queue\CleanOldItemsCommand;
use App\Context\Queue\QueueApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanOldItemsCommandTest extends TestCase
{
    public function testConfiguration(): void
    {
        $cmd = new CleanOldItemsCommand(
            $this->createMock(QueueApi::class),
        );

        self::assertSame(
            'queue:clean-old-items',
            $cmd->getName()
        );

        self::assertSame(
            'Cleans old queue items',
            $cmd->getDescription(),
        );
    }

    public function testWhenNoItems(): void
    {
        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('cleanOldItems')
            ->willReturn(0);

        $cmd = new CleanOldItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning old items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>There were no old items</>'
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
            ->method('cleanOldItems')
            ->willReturn(1);

        $cmd = new CleanOldItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning old items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>1 old item was cleaned</>'
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
            ->method('cleanOldItems')
            ->willReturn(2);

        $cmd = new CleanOldItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning old items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>2 old items were cleaned</>'
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
            ->method('cleanOldItems')
            ->willReturn(42);

        $cmd = new CleanOldItemsCommand($queueApi);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Cleaning old items...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>42 old items were cleaned</>'
            ));

        self::assertSame(0, $cmd->execute(
            $this->createMock(InputInterface::class),
            $output
        ));
    }
}
