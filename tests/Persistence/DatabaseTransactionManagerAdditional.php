<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Persistence\DatabaseTransactionManager;

class DatabaseTransactionManagerAdditional
{
    public function beginTransaction(DatabaseTransactionManager $manager): bool
    {
        return $manager->beginTransaction();
    }

    public function commit(DatabaseTransactionManager $manager): bool
    {
        return $manager->commit();
    }

    public function rollBack(DatabaseTransactionManager $manager): bool
    {
        return $manager->rollBack();
    }
}
