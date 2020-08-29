<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Series\Models\SeriesModel;
use App\Persistence\Episodes\EpisodeSeriesRecord;
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

class SaveEpisodeSeries
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
        /** @var EpisodeSeriesRecord[] $allPreviousSeries */
        $allPreviousSeries = $this->recordQueryFactory
            ->make(new EpisodeSeriesRecord())
            ->withWhere('episode_id', $episode->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousSeries,
            $episode
        );

        $this->insertNew(
            $allPreviousSeries,
            $episode
        );
    }

    /**
     * @param EpisodeSeriesRecord[] $allPreviousSeries
     */
    private function deleteNonExisting(
        array $allPreviousSeries,
        EpisodeModel $episode
    ): void {
        if (count($allPreviousSeries) < 1) {
            return;
        }

        $currentSeries = $episode->series;

        $allCurrentIds = array_map(
            static fn (SeriesModel $m) => $m->id,
            $currentSeries
        );

        $toDelete = [];

        foreach ($allPreviousSeries as $series) {
            if (
                in_array(
                    $series->series_id,
                    $allCurrentIds,
                    true,
                )
            ) {
                continue;
            }

            $toDelete[] = $series->series_id;
        }

        if (count($toDelete) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($toDelete), '?')
        );

        $statement = $this->pdo->prepare(
            'DELETE FROM ' . EpisodeSeriesRecord::tableName() .
            ' WHERE series_id IN (' . $in . ') ' .
            ' AND episode_id = ?'
        );

        $toDelete[] = $episode->id;

        $statement->execute($toDelete);
    }

    /**
     * @param EpisodeSeriesRecord[] $allPreviousSeries
     */
    private function insertNew(
        array $allPreviousSeries,
        EpisodeModel $episode
    ): void {
        $newEpisodeSeries = $episode->series;

        if (count($newEpisodeSeries) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (EpisodeSeriesRecord $r) => $r->series_id,
            $allPreviousSeries,
        );

        array_walk(
            $newEpisodeSeries,
            function (
                SeriesModel $series
            ) use (
                $existingIds,
                $episode
            ): void {
                if (
                    in_array(
                        $series->id,
                        $existingIds,
                        true,
                    )
                ) {
                    return;
                }

                $record = new EpisodeSeriesRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->episode_id = $episode->id;

                $record->series_id = $series->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
