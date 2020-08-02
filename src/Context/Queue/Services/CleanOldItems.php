<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Persistence\Constants;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;
use DateTimeZone;
use PDO;
use Safe\DateTimeImmutable;

use function array_fill;
use function array_map;
use function count;
use function implode;

class CleanOldItems
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

    public function __invoke(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $eightDaysAgo = new DateTimeImmutable(
            '8 days ago',
            new DateTimeZone('UTC')
        );

        /** @var QueueRecord[] $records */
        $records = ($this->recordQueryFactory)(
            new QueueRecord()
        )
            ->withWhere('is_finished', '1')
            ->withWhere(
                'finished_at',
                $eightDaysAgo->format(
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
            'DELETE FROM ' . (new QueueRecord())->getTableName() .
            ' WHERE id IN (' . $in . ')',
        );

        $queueStatement->execute($ids);

        $itemStatement = $this->pdo->prepare(
            'DELETE FROM ' . (new QueueItemRecord())->getTableName() .
            ' WHERE queue_id IN (' . $in . ')',
        );

        $itemStatement->execute($ids);

        return $total;
    }
}
