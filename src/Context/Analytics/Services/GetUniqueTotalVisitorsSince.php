<?php

declare(strict_types=1);

namespace App\Context\Analytics\Services;

use App\Persistence\Analytics\AnalyticsRecord;
use App\Persistence\Constants;
use DateTimeImmutable;
use PDO;
use PDOStatement;

use function assert;

class GetUniqueTotalVisitorsSince
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(?DateTimeImmutable $date = null): int
    {
        $table = AnalyticsRecord::tableName();

        $qs = 'SELECT count(DISTINCT cookie_id) FROM ' . $table;

        if ($date === null) {
            $query = $this->pdo->query($qs);

            assert($query instanceof PDOStatement);

            return (int) $query->fetchColumn();
        }

        $qs .= ' WHERE date > ?';

        $timeString = $date->format(Constants::POSTGRES_OUTPUT_FORMAT);

        $statement = $this->pdo->prepare($qs);

        $statement->execute([$timeString]);

        return (int) $statement->fetchColumn();
    }
}
