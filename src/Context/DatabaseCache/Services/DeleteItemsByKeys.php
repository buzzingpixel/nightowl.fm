<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Services;

use App\Persistence\DatabaseCache\CachePoolRecord;
use PDO;

use function array_fill;
use function count;
use function implode;

class DeleteItemsByKeys
{
    private PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    /**
     * @param string[] $keys
     */
    public function delete(array $keys): void
    {
        if (count($keys) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($keys), '?')
        );

        $itemStatement = $this->pdo->prepare(
            'DELETE FROM ' . CachePoolRecord::tableName() .
            ' WHERE key IN (' . $in . ')'
        );

        $itemStatement->execute($keys);
    }
}
