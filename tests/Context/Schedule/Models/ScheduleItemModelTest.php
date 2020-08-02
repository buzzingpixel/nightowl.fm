<?php

declare(strict_types=1);

namespace Tests\Context\Schedule\Models;

use App\Context\Schedule\Models\ScheduleItemModel;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ScheduleItemModelTest extends TestCase
{
    public function testRunEveryInvalidValue(): void
    {
        $model = new ScheduleItemModel();

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'RunEvery must be a float, integer, or string'
        );

        $model->checkRunEveryValue(null);
    }
}
