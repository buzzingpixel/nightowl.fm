<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Transformers;

use App\Context\DatabaseCache\DatabaseCacheItem;
use App\Persistence\DatabaseCache\CachePoolRecord;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

use function assert;
use function serialize;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class CacheItemToRecord
{
    public function transform(CacheItemInterface $cacheItem): CachePoolRecord
    {
        assert($cacheItem instanceof DatabaseCacheItem);

        $record = new CachePoolRecord();

        $record->id = $cacheItem->id;

        $record->key = $cacheItem->getKey();

        $record->value = serialize($cacheItem->get());

        if ($cacheItem->expiresAt !== null) {
            $record->expires_at = $cacheItem->expiresAt->format(
                DateTimeInterface::ATOM
            );
        }

        return $record;
    }
}
