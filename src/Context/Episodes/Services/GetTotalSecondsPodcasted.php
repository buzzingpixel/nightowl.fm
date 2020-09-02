<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\EpisodeConstants;
use App\Persistence\Episodes\EpisodeRecord;
use PDO;

class GetTotalSecondsPodcasted
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(): int
    {
        $tmp = $this->pdo->query(
            'SELECT SUM (file_runtime_seconds) AS total ' .
            'FROM ' . EpisodeRecord::tableName() . ' ' .
            "WHERE status = '" . EpisodeConstants::EPISODE_STATUS_LIVE . "'",
        );

        /** @phpstan-ignore-next-line */
        return (int) ($tmp->fetch()['total'] ?? 0);
    }
}
