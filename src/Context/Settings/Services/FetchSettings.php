<?php

declare(strict_types=1);

namespace App\Context\Settings\Services;

use App\Context\Settings\Models\FetchModel;
use App\Context\Settings\Models\SettingModel;
use App\Context\Settings\Transformers\RecordToModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Settings\SettingRecord;
use Throwable;

use function array_map;
use function count;

class FetchSettings
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToModel      = $recordToModel;
    }

    /**
     * @return SettingModel[]
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
     * @return SettingModel[]
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory->make(
            new SettingRecord()
        )
            ->withOrder('key', 'asc')
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN'
            );
        }

        if (count($fetchModel->keys) > 0) {
            $query = $query->withWhere(
                'key',
                $fetchModel->keys,
                'IN'
            );
        }

        $records = $query->all();

        /** @psalm-suppress MixedReturnTypeCoercion */
        return array_map(
            $this->recordToModel,
            $records
        );
    }
}
