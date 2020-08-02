<?php

declare(strict_types=1);

namespace App\Context\Schedule\Models;

use RuntimeException;
use Safe\DateTimeImmutable;

use function gettype;
use function in_array;

class ScheduleItemModel
{
    public string $id = '';

    public string $class = '';

    /** @var float|int|string  */
    public $runEvery = '';

    /**
     * @param float|int|string $runEvery
     */
    public function checkRunEveryValue($runEvery): void
    {
        $type = gettype($runEvery);

        $allowed = ['float', 'integer', 'string'];

        if (in_array($type, $allowed, true)) {
            return;
        }

        throw new RuntimeException(
            'RunEvery must be a float, integer, or string'
        );
    }

    public bool $isRunning = false;

    public ?DateTimeImmutable $lastRunStartAt = null;

    public ?DateTimeImmutable $lastRunEndAt = null;
}
