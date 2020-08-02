<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Services\RunItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;

class RunItemTest extends TestCase
{
    public function test(): void
    {
        $anonClass = new class ()
        {
            private bool $hasRun = false;

            /** @var mixed[] */
            private array $context = [];

            /**
             * @param mixed[] $context
             */
            public function testMethod(array $context): void
            {
                $this->hasRun  = true;
                $this->context = $context;
            }

            public function getHasRun(): bool
            {
                return $this->hasRun;
            }

            /**
             * @return mixed[]
             */
            public function getContext(): array
            {
                return $this->context;
            }
        };

        $queueItem          = new QueueItemModel();
        $queueItem->class   = 'testClass';
        $queueItem->method  = 'testMethod';
        $queueItem->context = ['test-context'];

        $di = $this->createMock(ContainerInterface::class);

        assert(
            $di instanceof ContainerInterface,
            $di instanceof MockObject,
        );

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo($queueItem->class))
            ->willReturn($anonClass);

        $service = new RunItem($di);

        $service($queueItem);

        self::assertTrue($anonClass->getHasRun());

        self::assertSame(['test-context'], $anonClass->getContext());
    }
}
