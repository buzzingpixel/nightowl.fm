<?php

declare(strict_types=1);

namespace Tests\Context\Events;

use App\Context\Events\StoppableEvent;
use PHPUnit\Framework\TestCase;

class StoppableEventTest extends TestCase
{
    public function test(): void
    {
        $event = new class extends StoppableEvent {
        };

        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $event->stopPropagation(false);

        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation(true);

        self::assertTrue($event->isPropagationStopped());
    }
}
