<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\People\Models\PersonModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\Shows\ShowHostsRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use PDO;

use function array_fill;
use function array_map;
use function array_walk;
use function count;
use function implode;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveShowHosts
{
    private RecordQueryFactory $recordQueryFactory;
    private SaveNewRecord $saveNewRecord;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private PDO $pdo;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        SaveNewRecord $saveNewRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        PDO $pdo
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->saveNewRecord      = $saveNewRecord;
        $this->uuidFactory        = $uuidFactory;
        $this->pdo                = $pdo;
    }

    public function save(ShowModel $show): void
    {
        /** @var ShowHostsRecord[] $allPreviousHosts */
        $allPreviousHosts = $this->recordQueryFactory
            ->make(new ShowHostsRecord())
            ->withWhere('show_id', $show->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousHosts,
            $show
        );

        $this->insertNew(
            $allPreviousHosts,
            $show
        );
    }

    /**
     * @param ShowHostsRecord[] $allPreviousHosts
     */
    private function deleteNonExisting(
        array $allPreviousHosts,
        ShowModel $show
    ): void {
        if (count($allPreviousHosts) < 1) {
            return;
        }

        $currentHosts = $show->hosts;

        $allCurrentIds = array_map(
            static fn (PersonModel $m) => $m->id,
            $currentHosts
        );

        $toDelete = [];

        foreach ($allPreviousHosts as $host) {
            if (in_array($host->person_id, $allCurrentIds)) {
                continue;
            }

            $toDelete[] = $host->person_id;
        }

        if (count($toDelete) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($toDelete), '?')
        );

        $statement = $this->pdo->prepare(
            'DELETE FROM ' . ShowHostsRecord::tableName() .
            ' WHERE person_id IN (' . $in . ') ' .
            ' AND show_id = ?'
        );

        $toDelete[] = $show->id;

        $statement->execute($toDelete);
    }

    /**
     * @param ShowHostsRecord[] $allPreviousHosts
     */
    private function insertNew(
        array $allPreviousHosts,
        ShowModel $show
    ): void {
        $newShowHosts = $show->hosts;

        if (count($newShowHosts) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (ShowHostsRecord $r) => $r->person_id,
            $allPreviousHosts,
        );

        array_walk(
            $newShowHosts,
            function (
                PersonModel $host
            ) use (
                $existingIds,
                $show
            ): void {
                if (in_array($host->id, $existingIds)) {
                    return;
                }

                $record = new ShowHostsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->show_id = $show->id;

                $record->person_id = $host->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
