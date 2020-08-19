<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\Shows\Models\ShowModel;
use App\Persistence\Shows\ShowHostsRecord;
use PDO;

class DeleteShowHosts
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete(ShowModel $show): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            ShowHostsRecord::tableName() .
            ' WHERE show_id=:show_id'
        );

        $statement->execute([':show_id' => $show->id]);
    }
}
