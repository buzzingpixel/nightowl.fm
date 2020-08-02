<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Persistence\Constants;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;
use DateTimeInterface;
use DateTimeZone;
use PDO;
use Safe\DateTimeImmutable;

use function array_fill;
use function array_map;
use function count;
use function implode;

class CleanDeadItems
{
    private RecordQueryFactory $recordQueryFactory;
    private PDO $pdo;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        PDO $pdo
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->pdo                = $pdo;
    }

    /**
     * Returns the number of dead items cleaned
     */
    public function __invoke(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        /** @var QueueRecord[] $records */
        $records = ($this->recordQueryFactory)(
            new QueueRecord()
        )
            ->withWhere('is_running', '1')
            ->withWhere(
                'assume_dead_after',
                $currentDate->format(
                    Constants::POSTGRES_OUTPUT_FORMAT
                ),
                '<'
            )
            ->all();

        $total = count($records);

        if ($total < 1) {
            return 0;
        }

        $ids = array_map(
            static fn (QueueRecord $r) => $r->id,
            $records
        );

        $in = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $queueStatement = $this->pdo->prepare(
            'UPDATE ' . (new QueueRecord())->getTableName() .
                ' SET is_running = false,' .
                ' is_finished = true,' .
                ' finished_due_to_error = true,' .
                ' error_message = \'Assumed dead\',' .
                ' finished_at = \'' .
                $currentDate->format(DateTimeInterface::ATOM) .
                '\'' .
                ' WHERE id IN (' . $in . ')',
        );

        $queueStatement->execute($ids);

        return $total;
    }
}
