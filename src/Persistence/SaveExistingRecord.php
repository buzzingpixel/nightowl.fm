<?php

declare(strict_types=1);

namespace App\Persistence;

use App\Payload\Payload;
use Exception;
use PDO;
use Throwable;

use function implode;

class SaveExistingRecord
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Record $record): Payload
    {
        if (! $record->id) {
            return new Payload(
                Payload::STATUS_NOT_UPDATED,
                ['message' => 'A record ID is required']
            );
        }

        try {
            $setSql = [];

            foreach ($record->getFields() as $field) {
                if ($field === 'id') {
                    continue;
                }

                $setSql[] = $field . '=:' . $field;
            }

            $sql = 'UPDATE ' . $record->getTableName() . ' SET ' .
                implode(', ', $setSql) .
                ' WHERE id=:id';

            $statement = $this->pdo->prepare($sql);

            $success = $statement->execute(
                $record->getBindValues()
            );

            if (! $success) {
                throw new Exception();
            }

            return new Payload(Payload::STATUS_UPDATED);
        } catch (Throwable $e) {
            return new Payload(
                Payload::STATUS_NOT_UPDATED,
                ['message' => 'An unknown error occurred']
            );
        }
    }
}
