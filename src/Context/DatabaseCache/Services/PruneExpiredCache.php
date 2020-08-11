<?php

declare(strict_types=1);

namespace App\Context\DatabaseCache\Services;

use App\Persistence\Constants;
use App\Persistence\DatabaseCache\CachePoolRecord;
use App\Utilities\SystemClock;
use DateTimeZone;
use PDO;

class PruneExpiredCache
{
    private PDO $pdo;
    private SystemClock $systemClock;

    public function __construct(PDO $pdo, SystemClock $systemClock)
    {
        $this->pdo         = $pdo;
        $this->systemClock = $systemClock;
    }

    public function __invoke(): void
    {
        $datetime = $this->systemClock->getCurrentTime()
            ->setTimezone(new DateTimeZone('UTC'));

        $format = $datetime->format(Constants::POSTGRES_OUTPUT_FORMAT);

        $statement = $this->pdo->prepare(
            'DELETE FROM ' . CachePoolRecord::tableName() .
            ' WHERE expires_at < ?'
        );

        $statement->execute([$format]);
    }
}
