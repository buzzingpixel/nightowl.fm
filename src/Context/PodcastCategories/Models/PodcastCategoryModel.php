<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Models;

use LogicException;

use function array_map;
use function array_merge;
use function array_walk;
use function end;
use function implode;
use function in_array;
use function ucfirst;

/**
 * @property-read PodcastCategoryModel|null $parent
 * @property-read PodcastCategoryModel[] $parentChain
 * @property PodcastCategoryModel[] $children
 */
class PodcastCategoryModel
{
    private const HAS_GET = [
        'parent',
        'parentChain',
        'children',
    ];

    private const HAS_SET = ['children'];

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

    /** @var PodcastCategoryModel[] */
    private array $children = [];

    protected function addChildItem(PodcastCategoryModel $item): void
    {
        $this->children[] = $item;
    }

    /**
     * @param PodcastCategoryModel[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = [];

        array_walk(
            $children,
            [$this, 'addChildItem'],
        );
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public string $name = '';

    public function __isset(string $name): bool
    {
        return in_array(
            $name,
            self::HAS_GET,
            true,
        );
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (
            ! in_array(
                $name,
                self::HAS_GET,
                true,
            )
        ) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'get' . ucfirst($name);

        /** @phpstan-ignore-next-line  */
        return $this->{$method}();
    }

    /**
     * @param mixed $val
     */
    public function __set(string $name, $val): void
    {
        if (
            ! in_array(
                $name,
                self::HAS_SET,
                true,
            )
        ) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'set' . ucfirst($name);

        /** @phpstan-ignore-next-line  */
        $this->{$method}($val);
    }
}
