<?php

declare(strict_types=1);

namespace App\Context\Queue\Models;

use Safe\DateTimeImmutable;

class QueueItemModel
{
    public string $id = '';

    public QueueModel $queue;

    public int $runOrder = 1;

    public bool $isFinished = false;

    public ?DateTimeImmutable $finishedAt = null;

    public string $class = '';

    public string $method = '__invoke';

    /** @var mixed[]|null */
    public ?array $context = null;
}
