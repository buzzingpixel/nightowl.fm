<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use PDO;
use Throwable;

class FetchTotalUsers
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(): int
    {
        try {
            return $this->run();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function run(): int
    {
        $query = $this->pdo->prepare('SELECT COUNT(*) from users');

        $query->execute();

        return (int) $query->fetchColumn();
    }
}
