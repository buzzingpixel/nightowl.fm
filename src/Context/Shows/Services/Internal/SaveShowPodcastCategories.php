<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\Shows\ShowPodcastCategoriesRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use PDO;

use function array_fill;
use function array_map;
use function array_walk;
use function count;
use function implode;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveShowPodcastCategories
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

    /**
     * @throws Exception
     */
    public function save(ShowModel $show): void
    {
        /** @var ShowPodcastCategoriesRecord[] $allPrevious */
        $allPrevious = $this->recordQueryFactory
            ->make(new ShowPodcastCategoriesRecord())
            ->withWhere('show_id', $show->id)
            ->all();

        $this->deleteNonExisting(
            $allPrevious,
            $show
        );

        $this->insertNew(
            $allPrevious,
            $show
        );
    }

    /**
     * @param ShowPodcastCategoriesRecord[] $allPrevious
     */
    private function deleteNonExisting(
        array $allPrevious,
        ShowModel $show
    ): void {
        if (count($allPrevious) < 1) {
            return;
        }

        $currentCategories = $show->podcastCategories;

        $allCurrentIds = array_map(
            static fn (PodcastCategoryModel $m) => $m->id,
            $currentCategories
        );

        $toDelete = [];

        foreach ($allPrevious as $category) {
            if (
                in_array(
                    $category->podcast_category_id,
                    $allCurrentIds,
                    true,
                )
            ) {
                continue;
            }

            $toDelete[] = $category->podcast_category_id;
        }

        if (count($toDelete) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($toDelete), '?')
        );

        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            ShowPodcastCategoriesRecord::tableName() .
            ' WHERE person_id IN (' . $in . ') ' .
            ' AND show_id = ?'
        );

        $toDelete[] = $show->id;

        $statement->execute($toDelete);
    }

    /**
     * @param ShowPodcastCategoriesRecord[] $allPrevious
     *
     * @throws Exception
     */
    private function insertNew(
        array $allPrevious,
        ShowModel $show
    ): void {
        $newShowCategories = $show->podcastCategories;

        if (count($newShowCategories) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (ShowPodcastCategoriesRecord $r) => $r->podcast_category_id,
            $allPrevious,
        );

        array_walk(
            $newShowCategories,
            function (
                PodcastCategoryModel $category
            ) use (
                $existingIds,
                $show
            ): void {
                if (
                    in_array(
                        $category->id,
                        $existingIds,
                        true,
                    )
                ) {
                    return;
                }

                $record = new ShowPodcastCategoriesRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->show_id = $show->id;

                $record->podcast_category_id = $category->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
