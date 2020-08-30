<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Services;

use App\Context\PodcastCategories\Models\FetchModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\Transformers\RecordToModel;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;
use App\Persistence\RecordQueryFactory;
use Throwable;

use function array_filter;
use function array_slice;
use function count;
use function in_array;
use function Safe\json_decode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchPodcastCategories
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;

    private bool $hasPopulated = false;

    /** @var PodcastCategoryRecord[] */
    private array $allCategoryRecords = [];

    /** @var array<string, PodcastCategoryModel> */
    private array $categoryModelsByIdKey = [];

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToModel      = $recordToModel;
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function fetch(?FetchModel $fetchModel = null): array
    {
        try {
            return $this->innerRun($fetchModel);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @return PodcastCategoryModel[]
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $this->populateAllCategories();

        $fetchModel ??= new FetchModel();

        $toReturn = $this->categoryModelsByIdKey;

        if ($fetchModel->hierarchical) {
            $toReturn = array_filter(
                $toReturn,
                static fn (PodcastCategoryModel $m) => $m->parent === null,
            );
        }

        if (count($fetchModel->ids) > 0) {
            $toReturn = array_filter(
                $toReturn,
                static fn (PodcastCategoryModel $m) => in_array(
                    $m->id,
                    $fetchModel->ids,
                    true,
                ),
            );
        }

        if (count($fetchModel->parentIds) > 0) {
            $toReturn = array_filter(
                $toReturn,
                static fn (PodcastCategoryModel $m) => in_array(
                    $m->parent !== null ? $m->parent->id : '',
                    $fetchModel->parentIds,
                    true,
                ),
            );
        }

        if (count($fetchModel->names) > 0) {
            $toReturn = array_filter(
                $toReturn,
                static fn (PodcastCategoryModel $m) => in_array(
                    $m->name,
                    $fetchModel->names,
                    true,
                ),
            );
        }

        return array_slice(
            $toReturn,
            $fetchModel->offset,
            $fetchModel->limit,
        );
    }

    private function populateAllCategories(): void
    {
        if ($this->hasPopulated) {
            return;
        }

        /** @var PodcastCategoryRecord[] $allCategoryRecords */
        $allCategoryRecords = $this->recordQueryFactory
            ->make(new PodcastCategoryRecord())
            ->withOrder('name', 'asc')
            ->all();

        $this->allCategoryRecords = $allCategoryRecords;

        $this->transformRecords();

        $this->hasPopulated = true;
    }

    private function transformRecords(): void
    {
        $hasProcessedAll = true;

        foreach ($this->allCategoryRecords as $record) {
            if (isset($this->categoryModelsByIdKey[$record->id])) {
                continue;
            }

            if ($this->transformRecord($record)) {
                continue;
            }

            $hasProcessedAll = false;
        }

        if ($hasProcessedAll) {
            return;
        }

        $this->transformRecords();
    }

    private function transformRecord(PodcastCategoryRecord $record): bool
    {
        /** @var string[] $chainIds */
        $chainIds = json_decode(
            $record->parent_chain,
            true,
        );

        $chain = [];

        foreach ($chainIds as $chainId) {
            if (! isset($this->categoryModelsByIdKey[$chainId])) {
                return false;
            }

            $chain[] = $this->categoryModelsByIdKey[$chainId];
        }

        $this->categoryModelsByIdKey[$record->id] = $this->recordToModel
            ->transform($record, $chain);

        return true;
    }
}
