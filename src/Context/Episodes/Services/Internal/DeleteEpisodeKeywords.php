<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Episodes\EpisodeKeywordsRecord;
use PDO;

class DeleteEpisodeKeywords
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete(EpisodeModel $episode): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . EpisodeKeywordsRecord::tableName() .
            ' WHERE episode_id = :id',
        );

        $statement->execute(['id' => $episode->id]);
    }
}
