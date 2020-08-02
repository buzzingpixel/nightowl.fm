<?php

declare(strict_types=1);

namespace App\Context\Queue\Models;

use DateTimeZone;
use RuntimeException;
use Safe\DateTimeImmutable;

use function assert;
use function is_array;

/**
 * @property QueueItemModel[] $items
 */
class QueueModel
{
    public function __construct()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->addedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assumeDeadAfter = new DateTimeImmutable(
            '+5 minutes',
            new DateTimeZone('UTC')
        );

        $this->initialAssumeDeadAfter = $this->assumeDeadAfter;
    }

    public string $id = '';

    public string $handle = '';

    public string $displayName = '';

    public bool $hasStarted = false;

    public bool $isRunning = false;

    public DateTimeImmutable $assumeDeadAfter;

    public DateTimeImmutable $initialAssumeDeadAfter;

    public bool $isFinished = false;

    public bool $finishedDueToError = false;

    public string $errorMessage = '';

    public float $percentComplete = 0.0;

    public DateTimeImmutable $addedAt;

    public ?DateTimeImmutable $finishedAt = null;

    /** @var QueueItemModel[] */
    private array $items = [];

    public function addItem(QueueItemModel $item): void
    {
        $item->queue = $this;

        $this->items[] = $item;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        if ($name !== 'items') {
            throw new RuntimeException('Property does not exist');
        }

        assert(is_array($value));

        /** @psalm-suppress MixedAssignment */
        foreach ($value as $item) {
            assert($item instanceof QueueItemModel);

            $this->addItem($item);
        }
    }

    public function __isset(string $name): bool
    {
        return $name === 'items';
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name !== 'items') {
            throw new RuntimeException('Property does not exist');
        }

        return $this->items;
    }
}
