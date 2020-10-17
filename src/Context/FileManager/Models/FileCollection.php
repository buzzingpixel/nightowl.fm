<?php

declare(strict_types=1);

namespace App\Context\FileManager\Models;

use Ramsey\Collection\AbstractCollection;

use function array_slice;

/**
 * @psalm-suppress MoreSpecificImplementedParamType
 * @method bool add(FileModel $fileModel)
 * @method FileModel first()
 * @method FileModel last()
 * @method FileCollection sort(string $propertyOrMethod, string $order = self::SORT_ASC)
 * @method FileCollection filter(callable $callback)
 * @method FileCollection where(string $propertyOrMethod, $value)
 * @method FileCollection map(callable $callback)
 * @method FileModel[] toArray()
 */
class FileCollection extends AbstractCollection
{
    public function getType(): string
    {
        return FileModel::class;
    }

    public function slice(
        ?int $length = null,
        ?int $offset = 0
    ): FileCollection {
        $collection = clone $this;

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress PossiblyNullArgument
         */
        $collection->data = array_slice(
            $collection->data,
            $offset,
            $length,
        );

        return $collection;
    }
}
