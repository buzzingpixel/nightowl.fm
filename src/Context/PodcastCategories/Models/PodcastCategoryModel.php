<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Models;

use LogicException;

use function array_map;
use function array_merge;
use function end;
use function implode;

/**
 * @property-read PodcastCategoryModel|null $parent
 * @property-read PodcastCategoryModel[] $parentChain
 */
class PodcastCategoryModel
{
    /**
     * @param PodcastCategoryModel[] $parentChain
     */
    public function __construct(array $parentChain = [])
    {
        foreach ($parentChain as $parent) {
            $this->addParentChainItem($parent);
        }

        $parent = end($parentChain);

        if ($parent === false) {
            return;
        }

        $this->parent = $parent;
    }

    public string $id = '';

    private ?PodcastCategoryModel $parent = null;

    public function getParent(): ?PodcastCategoryModel
    {
        return $this->parent;
    }

    /** @var PodcastCategoryModel[] */
    private array $parentChain = [];

    protected function addParentChainItem(PodcastCategoryModel $item): void
    {
        $this->parentChain[] = $item;
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function getParentChain(): array
    {
        return $this->parentChain;
    }

    public function getParentChainAsPath(): string
    {
        $pathArray = array_map(
            static fn (PodcastCategoryModel $m) => $m->name,
            $this->parentChain,
        );

        return implode('/', $pathArray);
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function getParentChainWithSelf(): array
    {
        return array_merge($this->parentChain, [$this]);
    }

    public function getParentChainWithSelfAsPath(): string
    {
        $pathArray = array_map(
            static fn (PodcastCategoryModel $m) => $m->name,
            $this->getParentChainWithSelf(),
        );

        return implode('/', $pathArray);
    }

    public string $name = '';

    public function __isset(string $name): bool
    {
        return $name === 'parentChain' || $name === 'parent';
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name !== 'parentChain' && $name !== 'parent') {
            throw new LogicException(
                'Invalid property'
            );
        }

        /** @phpstan-ignore-next-line */
        return $this->{$name};
    }
}
