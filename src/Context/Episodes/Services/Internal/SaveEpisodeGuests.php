<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\People\Models\PersonModel;
use App\Persistence\Episodes\EpisodeGuestsRecord;
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

class SaveEpisodeGuests
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
        /** @var EpisodeGuestsRecord[] $allPreviousGuests */
        $allPreviousGuests = $this->recordQueryFactory
            ->make(new EpisodeGuestsRecord())
            ->withWhere('episode_id', $episode->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousGuests,
            $episode
        );

        $this->insertNew(
            $allPreviousGuests,
            $episode
        );
    }

    /**
     * @param EpisodeGuestsRecord[] $allPreviousGuests
     */
    private function deleteNonExisting(
        array $allPreviousGuests,
        EpisodeModel $episode
    ): void {
        if (count($allPreviousGuests) < 1) {
            return;
        }

        $currentGuests = $episode->guests;

        $allCurrentIds = array_map(
            static fn (PersonModel $m) => $m->id,
            $currentGuests
        );

        $toDelete = [];

        foreach ($allPreviousGuests as $guest) {
            if (in_array($guest->person_id, $allCurrentIds)) {
                continue;
            }

            $toDelete[] = $guest->person_id;
        }

        if (count($toDelete) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($toDelete), '?')
        );

        $statement = $this->pdo->prepare(
            'DELETE FROM ' . EpisodeGuestsRecord::tableName() .
            ' WHERE person_id IN (' . $in . ') ' .
            ' AND episode_id = ?'
        );

        $toDelete[] = $episode->id;

        $statement->execute($toDelete);
    }

    /**
     * @param EpisodeGuestsRecord[] $allPreviousGuests
     */
    private function insertNew(
        array $allPreviousGuests,
        EpisodeModel $episode
    ): void {
        $newEpisodeGuests = $episode->guests;

        if (count($newEpisodeGuests) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (EpisodeGuestsRecord $r) => $r->person_id,
            $allPreviousGuests,
        );

        array_walk(
            $newEpisodeGuests,
            function (
                PersonModel $guest
            ) use (
                $existingIds,
                $episode
            ): void {
                if (in_array($guest->id, $existingIds)) {
                    return;
                }

                $record = new EpisodeGuestsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->episode_id = $episode->id;

                $record->person_id = $guest->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
