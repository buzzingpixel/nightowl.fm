<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\People\Models\PersonModel;
use App\Persistence\Episodes\EpisodeHostsRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use PDO;

use function array_fill;
use function array_map;
use function array_walk;
use function count;
use function implode;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveEpisodeHosts
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

    public function save(EpisodeModel $episode): void
    {
        /** @var EpisodeHostsRecord[] $allPreviousHosts */
        $allPreviousHosts = $this->recordQueryFactory
            ->make(new EpisodeHostsRecord())
            ->withWhere('episode_id', $episode->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousHosts,
            $episode
        );

        $this->insertNew(
            $allPreviousHosts,
            $episode
        );
    }

    /**
     * @param EpisodeHostsRecord[] $allPreviousHosts
     */
    private function deleteNonExisting(
        array $allPreviousHosts,
        EpisodeModel $episode
    ): void {
        if (count($allPreviousHosts) < 1) {
            return;
        }

        $currentHosts = $episode->hosts;

        $allCurrentIds = array_map(
            static fn (PersonModel $m) => $m->id,
            $currentHosts
        );

        $toDelete = [];

        foreach ($allPreviousHosts as $host) {
            if (
                in_array(
                    $host->person_id,
                    $allCurrentIds,
                    true,
                )
            ) {
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
            'DELETE FROM ' . EpisodeHostsRecord::tableName() .
            ' WHERE person_id IN (' . $in . ') ' .
            ' AND episode_id = ?'
        );

        $toDelete[] = $episode->id;

        $statement->execute($toDelete);
    }

    /**
     * @param EpisodeHostsRecord[] $allPreviousHosts
     */
    private function insertNew(
        array $allPreviousHosts,
        EpisodeModel $episode
    ): void {
        $newEpisodeHosts = $episode->hosts;

        if (count($newEpisodeHosts) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (EpisodeHostsRecord $r) => $r->person_id,
            $allPreviousHosts,
        );

        array_walk(
            $newEpisodeHosts,
            function (
                PersonModel $host
            ) use (
                $existingIds,
                $episode
            ): void {
                if (
                    in_array(
                        $host->id,
                        $existingIds,
                        true,
                    )
                ) {
                    return;
                }

                $record = new EpisodeHostsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->episode_id = $episode->id;

                $record->person_id = $host->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
