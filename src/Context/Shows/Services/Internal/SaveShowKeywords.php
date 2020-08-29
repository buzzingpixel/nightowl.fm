<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\Shows\ShowKeywordsRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use PDO;

use function array_fill;
use function array_map;
use function array_walk;
use function count;
use function implode;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveShowKeywords
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
        /** @var ShowKeywordsRecord[] $allPreviousKeywords */
        $allPreviousKeywords = $this->recordQueryFactory
            ->make(new ShowKeywordsRecord())
            ->withWhere('show_id', $show->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousKeywords,
            $show
        );

        $this->insertNew(
            $allPreviousKeywords,
            $show
        );
    }

    /**
     * @param ShowKeywordsRecord[] $allPreviousKeywords
     */
    private function deleteNonExisting(
        array $allPreviousKeywords,
        ShowModel $show
    ): void {
        if (count($allPreviousKeywords) < 1) {
            return;
        }

        $currentKeywords = $show->keywords;

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
            'DELETE FROM ' . ShowKeywordsRecord::tableName() .
            ' WHERE keyword_id IN (' . $in . ') ' .
            ' AND show_id = ?'
        );

        $toDelete[] = $show->id;

        $statement->execute($toDelete);
    }

    /**
     * @param ShowKeywordsRecord[] $allPreviousKeywords
     */
    private function insertNew(
        array $allPreviousKeywords,
        ShowModel $show
    ): void {
        $newShowKeywords = $show->keywords;

        if (count($newShowKeywords) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (ShowKeywordsRecord $r) => $r->keyword_id,
            $allPreviousKeywords,
        );

        array_walk(
            $newShowKeywords,
            function (
                KeywordModel $keyword
            ) use (
                $existingIds,
                $show
            ): void {
                if (
                    in_array(
                        $keyword->id,
                        $existingIds,
                        true
                    )
                ) {
                    return;
                }

                $record = new ShowKeywordsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->show_id = $show->id;

                $record->keyword_id = $keyword->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
