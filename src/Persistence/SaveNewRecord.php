<?php

declare(strict_types=1);

namespace App\Persistence;

use App\Payload\Payload;
use Exception;
use PDO;
use Throwable;

use function implode;

class SaveNewRecord
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Record $record): Payload
    {
        return $this->save($record);
    }

    public function save(Record $record): Payload
    {
        $into = implode(', ', $record->getFields());

        $values = implode(
            ', ',
            $record->getFields(true)
        );

        if (! $record->id) {
            return new Payload(
                Payload::STATUS_NOT_CREATED,
                ['message' => 'A record ID is required']
            );
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO ' . $record->getTableName() .
                    ' (' . $into . ') VALUES (' . $values . ')'
            );

            $success = $statement->execute(
                $record->getBindValues()
            );

            if (! $success) {
                throw new Exception();
            }

            return new Payload(Payload::STATUS_CREATED, [
                'message' => 'Created record with id ' . $record->id,
                'id' => $record->id,
            ]);
        } catch (Throwable $e) {
            return new Payload(
                Payload::STATUS_NOT_CREATED,
                ['message' => 'An unknown error occurred']
            );
        }
    }
}
