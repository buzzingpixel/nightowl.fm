<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Transformers;

use App\Context\DatabaseCache\DatabaseCacheItem;
use App\Persistence\Constants;
use App\Persistence\DatabaseCache\CachePoolRecord;
use DateTime;
use Psr\Cache\CacheItemInterface;

use function unserialize;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class CacheRecordToItem
{
    public function transform(CachePoolRecord $record): CacheItemInterface
    {
        $item = new DatabaseCacheItem();

        $item->id = $record->id;

        $item->key = $record->key;

        $item->value = unserialize($record->value);

        if ($record->expires_at !== null && $record->expires_at !== '') {
            /** @phpstan-ignore-next-line */
            $item->expiresAt = DateTime::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                $record->expires_at,
            );
        }

        return $item;
    }
}
