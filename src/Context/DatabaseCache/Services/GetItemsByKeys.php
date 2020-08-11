<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Services;

use App\Context\DatabaseCache\DatabaseCacheItem;
use App\Context\DatabaseCache\Transformers\CacheRecordToItem;
use App\Persistence\DatabaseCache\CachePoolRecord;
use App\Persistence\RecordQueryFactory;
use Psr\Cache\CacheItemInterface;

class GetItemsByKeys
{
    private RecordQueryFactory $recordQueryFactory;
    private CacheRecordToItem $recordToItem;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        CacheRecordToItem $recordToItem
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToItem       = $recordToItem;
    }

    /**
     * @param string[] $keys
     *
     * @return CacheItemInterface[]
     */
    public function get(array $keys = []): array
    {
        /** @var CachePoolRecord[] $records */
        $records = $this->recordQueryFactory->make(
            new CachePoolRecord()
        )
            ->withWhere('key', $keys, 'IN')
            ->all();

        $cacheItems = [];

        foreach ($records as $record) {
            $cacheItems[$record->key] = $this->recordToItem->transform(
                $record
            );
        }

        foreach ($keys as $key) {
            if (isset($cacheItems[$key])) {
                continue;
            }

            $item = new DatabaseCacheItem();

            $item->key = $key;

            $cacheItems[$key] = $item;
        }

        return $cacheItems;
    }
}
