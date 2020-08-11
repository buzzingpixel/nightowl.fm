<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache;

use App\Context\DatabaseCache\Services\ClearAllCache;
use App\Context\DatabaseCache\Services\DeleteItemsByKeys;
use App\Context\DatabaseCache\Services\GetItemsByKeys;
use App\Context\DatabaseCache\Services\SaveItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

use function array_walk;
use function count;

class CacheItemPool implements CacheItemPoolInterface
{
    private GetItemsByKeys $getItemsByKeys;
    private SaveItem $saveItem;
    private ClearAllCache $clearAllCache;
    private DeleteItemsByKeys $deleteItemsByKeys;

    public function __construct(
        GetItemsByKeys $getItemsByKeys,
        SaveItem $saveItem,
        ClearAllCache $clearAllCache,
        DeleteItemsByKeys $deleteItemsByKeys
    ) {
        $this->getItemsByKeys    = $getItemsByKeys;
        $this->saveItem          = $saveItem;
        $this->clearAllCache     = $clearAllCache;
        $this->deleteItemsByKeys = $deleteItemsByKeys;
    }

    /** @var CacheItemInterface[] */
    private array $runTimeCache = [];

    /** @var CacheItemInterface[] */
    private array $deferred = [];

    /**
     * @param string[] $keys
     */
    private function populateItemsByKeys(array $keys = []): void
    {
        $items = $this->getItemsByKeys->get($keys);

        array_walk(
            $items,
            fn (CacheItemInterface $c) => $this->runTimeCache[$c->getKey()] = $c,
        );
    }

    /**
     * @inheritDoc
     */
    public function getItem($key): CacheItemInterface
    {
        if (isset($this->runTimeCache[$key])) {
            return $this->runTimeCache[$key];
        }

        $this->populateItemsByKeys([$key]);

        return $this->runTimeCache[$key];
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function getItems(array $keys = []): array
    {
        $toQueryKeys = [];

        $toReturnItems = [];

        foreach ($keys as $key) {
            if (! isset($this->runTimeCache[$key])) {
                $toQueryKeys[] = $key;

                continue;
            }

            $toReturnItems[$key] = $this->runTimeCache[$key];
        }

        if (count($toQueryKeys) < 1) {
            return $toReturnItems;
        }

        $this->populateItemsByKeys($keys);

        foreach ($toQueryKeys as $key) {
            $toReturnItems[$key] = $this->runTimeCache[$key];
        }

        return $toReturnItems;
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key): bool
    {
        if (isset($this->runTimeCache[$key])) {
            return $this->runTimeCache[$key]->isHit();
        }

        $this->populateItemsByKeys([$key]);

        return $this->runTimeCache[$key]->isHit();
    }

    public function clear(): bool
    {
        $this->clearAllCache->clear();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key): bool
    {
        $this->deleteItems([$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $this->deleteItemsByKeys->delete($keys);

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->runTimeCache[$item->getKey()] = $item;

        $this->saveItem->save($item);

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->runTimeCache[$item->getKey()] = $item;

        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    public function commit(): bool
    {
        if (count($this->deferred) < 1) {
            return true;
        }

        foreach ($this->deferred as $item) {
            $this->saveItem->save($item);
        }

        return true;
    }
}
