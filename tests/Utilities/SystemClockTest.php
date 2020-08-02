<?php

declare(strict_types=1);

namespace Tests\Utilities;

use App\Utilities\SystemClock;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Throwable;

class SystemClockTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testGetCurrentTime(): void
    {
        $dateTimeFromService = (new SystemClock())->getCurrentTime();

        $dateTime = new DateTimeImmutable();

        self::assertSame(
            $dateTime->format('Y-m-d H:i:s'),
            $dateTimeFromService->format('Y-m-d H:i:s')
        );
    }
}
