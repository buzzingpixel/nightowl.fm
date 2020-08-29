<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Persistence\Episodes\EpisodeKeywordsRecord;
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

class SaveEpisodeKeywords
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
        /** @var EpisodeKeywordsRecord[] $allPreviousKeywords */
        $allPreviousKeywords = $this->recordQueryFactory
            ->make(new EpisodeKeywordsRecord())
            ->withWhere('episode_id', $episode->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousKeywords,
            $episode
        );

        $this->insertNew(
            $allPreviousKeywords,
            $episode
        );
    }

    /**
     * @param EpisodeKeywordsRecord[] $allPreviousKeywords
     */
    private function deleteNonExisting(
        array $allPreviousKeywords,
        EpisodeModel $episode
    ): void {
        if (count($allPreviousKeywords) < 1) {
            return;
        }

        $currentKeywords = $episode->keywords;

        $allCurrentIds = array_map(
            static fn (KeywordModel $m) => $m->id,
            $currentKeywords
        );

        $toDelete = [];

        foreach ($allPreviousKeywords as $keyword) {
            if (
                in_array(
                    $keyword->keyword_id,
                    $allCurrentIds,
                    true,
                )
            ) {
                continue;
            }

            $toDelete[] = $keyword->keyword_id;
        }

        if (count($toDelete) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($toDelete), '?')
        );

        $statement = $this->pdo->prepare(
            'DELETE FROM ' . EpisodeKeywordsRecord::tableName() .
            ' WHERE keyword_id IN (' . $in . ') ' .
            ' AND episode_id = ?'
        );

        $toDelete[] = $episode->id;

        $statement->execute($toDelete);
    }

    /**
     * @param EpisodeKeywordsRecord[] $allPreviousKeywords
     */
    private function insertNew(
        array $allPreviousKeywords,
        EpisodeModel $episode
    ): void {
        $newEpisodeKeywords = $episode->keywords;

        if (count($newEpisodeKeywords) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (EpisodeKeywordsRecord $r) => $r->keyword_id,
            $allPreviousKeywords,
        );

        array_walk(
            $newEpisodeKeywords,
            function (
                KeywordModel $keyword
            ) use (
                $existingIds,
                $episode
            ): void {
                if (
                    in_array(
                        $keyword->id,
                        $existingIds,
                        true,
                    )
                ) {
                    return;
                }

                $record = new EpisodeKeywordsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->episode_id = $episode->id;

                $record->keyword_id = $keyword->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
