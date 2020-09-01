<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Safe\DateTimeImmutable;

use function is_int;
use function time;

class DatabaseCacheItem implements CacheItemInterface
{
    public string $id = '';

    public string $key = '';

    /** @var mixed */
    public $value;

    public ?DateTimeInterface $expiresAt = null;

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->value !== null;
    }

    /**
     * @inheritDoc
     * @psalm-suppress LessSpecificImplementedReturnType
     * @phpstan-ignore-next-line
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     * @psalm-suppress LessSpecificImplementedReturnType
     * @phpstan-ignore-next-line
     */
    public function expiresAt($expiration): self
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    /**
     * @inheritDoc
     * @psalm-suppress LessSpecificImplementedReturnType
     * @phpstan-ignore-next-line
     */
    public function expiresAfter($time): self
    {
        if ($time === null) {
            $this->expiresAt = null;

            return $this;
        }

        $expires = (new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        ));

        if ($time instanceof DateInterval) {
            $expires = $expires->add($time);

            $this->expiresAt = $expires;

            return $this;
        }

        if (is_int($time)) {
            $expires = $expires->setTimestamp(time() + $time);

            $this->expiresAt = $expires;

            return $this;
        }

        throw new InvalidArgumentException(
            '$time must be one of integer, \DateInterval, or null'
        );
    }
}
