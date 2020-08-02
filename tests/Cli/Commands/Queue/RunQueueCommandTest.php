<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Queue;

use App\Cli\Commands\Queue\RunQueueCommand;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;

class RunQueueCommandTest extends TestCase
{
    public function testWhenNoItemInQueue(): void
    {
        $msg = 'There are no items in the queue';

        $logger = $this->createMock(LoggerInterface::class);

        assert(
            $logger instanceof LoggerInterface,
            $logger instanceof MockObject,
        );

        $logger->expects(self::at(0))
            ->method('info')
            ->with(
                self::equalTo(
                    'Queue command is running next item in queue'
                ),
            );

        $logger->expects(self::at(1))
            ->method('info')
            ->with(self::equalTo($msg));

        $queueApi = $this->createMock(QueueApi::class);

        assert(
            $queueApi instanceof QueueApi,
            $queueApi instanceof MockObject,
        );

        $queueApi->expects(self::once())
            ->method('fetchNextQueueItem')
            ->willReturn(null);

        $cmd = new RunQueueCommand($queueApi, $logger);

        $input = $this->createMock(InputInterface::class);

        assert(
            $input instanceof InputInterface,
            $input instanceof MockObject,
        );

        $output = $this->createMock(OutputInterface::class);

        assert(
            $output instanceof OutputInterface,
            $output instanceof MockObject,
        );

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo('<fg=green>' . $msg . '</>'));

        self::assertSame(0, $cmd->execute($input, $output));
    }

    public function testSuccessfulRun(): void
    {
        $item           = new QueueItemModel();
        $item->id       = 'testItemId';
        $item->runOrder = 3;

        $queue         = new QueueModel();
        $queue->id     = 'testId';
        $queue->handle = 'testHandle';
        $queue->addItem($item);

        $msg = 'Running ' . $item->queue->handle .
            ' (' . $item->queue->id . ') step ' .
            ((string) $item->runOrder) . ' (' .
            $item->id . ')...';

        $msg2 = 'Finished';

        $logger = $this->createMock(LoggerInterface::class);

        assert(
            $logger instanceof LoggerInterface,
            $logger instanceof MockObject,
        );

        $logger->expects(self::at(0))
            ->method('info')
            ->with(
                self::equalTo(
                    'Queue command is running next item in queue'
                ),
            );

        $logger->expects(self::at(1))
            ->method('info')
            ->with(self::equalTo($msg));

        $logger->expects(self::at(2))
            ->method('info')
            ->with(self::equalTo($msg2));

        $queueApi = $this->createMock(QueueApi::class);

        assert(
            $queueApi instanceof QueueApi,
            $queueApi instanceof MockObject,
        );

        $queueApi->expects(self::at(0))
            ->method('fetchNextQueueItem')
            ->willReturn($item);

        $queueApi->expects(self::at(1))
            ->method('markAsStarted')
            ->with($queue);

        $queueApi->expects(self::at(2))
            ->method('runItem')
            ->with($item);

        $queueApi->expects(self::at(3))
            ->method('postRun')
            ->with($item);

        $cmd = new RunQueueCommand($queueApi, $logger);

        $input = $this->createMock(InputInterface::class);

        assert(
            $input instanceof InputInterface,
            $input instanceof MockObject,
        );

        $output = $this->createMock(OutputInterface::class);

        assert(
            $output instanceof OutputInterface,
            $output instanceof MockObject,
        );

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo('<fg=yellow>' . $msg . '</>'));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo('<fg=green>' . $msg2 . '</>'));

        self::assertSame(0, $cmd->execute($input, $output));
    }

    public function testException(): void
    {
        $exception = new Exception();

        $item           = new QueueItemModel();
        $item->id       = 'testItemId';
        $item->runOrder = 3;

        $queue         = new QueueModel();
        $queue->id     = 'testId';
        $queue->handle = 'testHandle';
        $queue->addItem($item);

        $msg = 'Running ' . $item->queue->handle .
            ' (' . $item->queue->id . ') step ' .
            ((string) $item->runOrder) . ' (' .
            $item->id . ')...';

        $msg2 = 'An exception was thrown running ' . $item->queue->handle .
            ' (' . $item->queue->id . ') step ' .
            ((string) $item->runOrder) . ' (' .
            $item->id . ')...';

        $logger = $this->createMock(LoggerInterface::class);

        assert(
            $logger instanceof LoggerInterface,
            $logger instanceof MockObject,
        );

        $logger->expects(self::at(0))
            ->method('info')
            ->with(
                self::equalTo(
                    'Queue command is running next item in queue'
                ),
            );

        $logger->expects(self::at(1))
            ->method('info')
            ->with(self::equalTo($msg));

        $logger->expects(self::at(2))
            ->method('error')
            ->with(
                self::equalTo($msg2),
                self::equalTo(['exception' => $exception]),
            );

        $queueApi = $this->createMock(QueueApi::class);

        assert(
            $queueApi instanceof QueueApi,
            $queueApi instanceof MockObject,
        );

        $queueApi->expects(self::at(0))
            ->method('fetchNextQueueItem')
            ->willReturn($item);

        $queueApi->expects(self::at(1))
            ->method('markAsStarted')
            ->with(self::equalTo($queue));

        $queueApi->expects(self::at(2))
            ->method('runItem')
            ->with(self::equalTo($item))
            ->willThrowException($exception);

        $queueApi->expects(self::at(3))
            ->method('markStoppedDueToError')
            ->with(
                self::equalTo($queue),
                self::equalTo($exception),
            );

        $cmd = new RunQueueCommand($queueApi, $logger);

        $input = $this->createMock(InputInterface::class);

        assert(
            $input instanceof InputInterface,
            $input instanceof MockObject,
        );

        $output = $this->createMock(OutputInterface::class);

        assert(
            $output instanceof OutputInterface,
            $output instanceof MockObject,
        );

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo('<fg=yellow>' . $msg . '</>'));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo('<fg=red>' . $msg2 . '</>'));

        self::assertSame(1, $cmd->execute($input, $output));
    }
}
