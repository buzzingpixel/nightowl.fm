<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Episodes\EpisodeGuestsRecord;
use PDO;

class DeleteEpisodeGuests
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete(EpisodeModel $episode): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . EpisodeGuestsRecord::tableName() .
            ' WHERE episode_id = :id',
        );

        $statement->execute(['id' => $episode->id]);
    }
}
