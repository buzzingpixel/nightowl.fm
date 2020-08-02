<?php

declare(strict_types=1);

namespace Tests\Context\Queue;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use App\Context\Queue\Services\AddToQueue;
use App\Context\Queue\Services\CleanDeadItems;
use App\Context\Queue\Services\CleanOldItems;
use App\Context\Queue\Services\ClearAllStalledItems;
use App\Context\Queue\Services\DeleteQueuesByIds;
use App\Context\Queue\Services\FetchIncomplete;
use App\Context\Queue\Services\FetchNextQueueItem;
use App\Context\Queue\Services\FetchStalledItems;
use App\Context\Queue\Services\MarkItemAsStarted;
use App\Context\Queue\Services\MarkStoppedDueToError;
use App\Context\Queue\Services\PostRun;
use App\Context\Queue\Services\RestartAllStalledItems;
use App\Context\Queue\Services\RestartQueuesByIds;
use App\Context\Queue\Services\RunItem;
use App\Payload\Payload;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;

class QueueApiTest extends TestCase
{
    public function testAddToQueue(): void
    {
        $payload = new Payload(Payload::STATUS_SUCCESSFUL);

        $queueModel = new QueueModel();

        $service = $this->createMock(AddToQueue::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueModel))
            ->willReturn($payload);

        assert(
            $service instanceof AddToQueue &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(AddToQueue::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame($payload, $api->addToQueue($queueModel));
    }

    public function testFetchNextQueueItem(): void
    {
        $queueItemModel = new QueueItemModel();

        $service = $this->createMock(FetchNextQueueItem::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturn($queueItemModel);

        assert(
            $service instanceof FetchNextQueueItem &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(FetchNextQueueItem::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame($queueItemModel, $api->fetchNextQueueItem());
    }

    public function testMarkAsStarted(): void
    {
        $queueModel = new QueueModel();

        $service = $this->createMock(MarkItemAsStarted::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueModel));

        assert(
            $service instanceof MarkItemAsStarted &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(MarkItemAsStarted::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->markAsStarted($queueModel);
    }

    public function testRunItem(): void
    {
        $queueItemModel = new QueueItemModel();

        $service = $this->createMock(RunItem::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueItemModel));

        assert(
            $service instanceof RunItem &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(RunItem::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->runItem($queueItemModel);
    }

    public function testPostRun(): void
    {
        $queueItemModel = new QueueItemModel();

        $service = $this->createMock(PostRun::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueItemModel));

        assert(
            $service instanceof PostRun &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(PostRun::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->postRun($queueItemModel);
    }

    public function testMarkStoppedDueToError(): void
    {
        $queueModel = new QueueModel();

        $exception = new Exception();

        $service = $this->createMock(MarkStoppedDueToError::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueModel), self::equalTo($exception));

        assert(
            $service instanceof MarkStoppedDueToError &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(MarkStoppedDueToError::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->markStoppedDueToError($queueModel, $exception);
    }

    public function testFetchStalledItems(): void
    {
        $queueModel1 = new QueueModel();
        $queueModel2 = new QueueModel();

        $service = $this->createMock(FetchStalledItems::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturn([$queueModel1, $queueModel2]);

        assert(
            $service instanceof FetchStalledItems &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(FetchStalledItems::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame(
            [$queueModel1, $queueModel2],
            $api->fetchStalledItems()
        );
    }

    public function testFetchIncomplete(): void
    {
        $queueModel1 = new QueueModel();
        $queueModel2 = new QueueModel();

        $service = $this->createMock(FetchIncomplete::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturn([$queueModel1, $queueModel2]);

        assert(
            $service instanceof FetchIncomplete &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(FetchIncomplete::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame(
            [$queueModel1, $queueModel2],
            $api->fetchIncomplete()
        );
    }

    public function testRestartQueuesByIds(): void
    {
        $ids = ['foo-id-1', 'foo-id-2'];

        $service = $this->createMock(RestartQueuesByIds::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with($ids);

        assert(
            $service instanceof RestartQueuesByIds &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(RestartQueuesByIds::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->restartQueuesByIds($ids);
    }

    public function testRestartAllStalledItems(): void
    {
        $service = $this->createMock(RestartAllStalledItems::class);

        $service->expects(self::once())
            ->method('__invoke');

        assert(
            $service instanceof RestartAllStalledItems &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(RestartAllStalledItems::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->restartAllStalledItems();
    }

    public function testDeleteQueuesByIds(): void
    {
        $ids = ['foo-id-1', 'foo-id-2'];

        $service = $this->createMock(DeleteQueuesByIds::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with($ids);

        assert(
            $service instanceof DeleteQueuesByIds &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(DeleteQueuesByIds::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->deleteQueuesByIds($ids);
    }

    public function testClearAllStalledItems(): void
    {
        $service = $this->createMock(ClearAllStalledItems::class);

        $service->expects(self::once())
            ->method('__invoke');

        assert(
            $service instanceof ClearAllStalledItems &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(ClearAllStalledItems::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        $api->clearAllStalledItems();
    }

    public function testCleanDeadItems(): void
    {
        $service = $this->createMock(CleanDeadItems::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturn(42);

        assert(
            $service instanceof CleanDeadItems &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(CleanDeadItems::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame(42, $api->cleanDeadItems());
    }

    public function testCleanOldItems(): void
    {
        $service = $this->createMock(CleanOldItems::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturn(42);

        assert(
            $service instanceof CleanOldItems &&
            $service instanceof MockObject,
        );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(CleanOldItems::class))
            ->willReturn($service);

        assert(
            $di instanceof ContainerInterface &&
            $di instanceof MockObject,
        );

        $api = new QueueApi($di);

        self::assertSame(42, $api->cleanOldItems());
    }
}
