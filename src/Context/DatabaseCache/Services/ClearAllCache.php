<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Services;

use App\Persistence\DatabaseCache\CachePoolRecord;
use PDO;

class ClearAllCache
{
    private PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    public function clear(): void
    {
        $this->pdo->query('TRUNCATE ' . CachePoolRecord::tableName());
    }
}
