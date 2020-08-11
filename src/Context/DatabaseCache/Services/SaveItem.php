<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Services;

use App\Context\DatabaseCache\DatabaseCacheItem;
use App\Context\DatabaseCache\Transformers\CacheItemToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Psr\Cache\CacheItemInterface;

use function assert;

class SaveItem
{
    private SaveNewRecord $saveNewRecord;
    private SaveExistingRecord $saveExistingRecord;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private CacheItemToRecord $itemToRecord;

    public function __construct(
        SaveNewRecord $saveNewRecord,
        SaveExistingRecord $saveExistingRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        CacheItemToRecord $itemToRecord
    ) {
        $this->saveNewRecord      = $saveNewRecord;
        $this->saveExistingRecord = $saveExistingRecord;
        $this->uuidFactory        = $uuidFactory;
        $this->itemToRecord       = $itemToRecord;
    }

    public function save(CacheItemInterface $cacheItem): Payload
    {
        assert($cacheItem instanceof DatabaseCacheItem);

        $isNew = false;

        if ($cacheItem->id === '') {
            $cacheItem->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $record = $this->itemToRecord->transform($cacheItem);

        return $isNew ?
            $this->saveNewRecord->save($record) :
            $this->saveExistingRecord->save($record);
    }
}
