<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\Transformers\RecordToModel;
use App\Persistence\People\PersonRecord;
use App\Persistence\RecordQueryFactory;
use Throwable;

use function array_map;
use function count;

class FetchPeople
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
     * @return PersonModel[]
     *
     * @psalm-suppress MixedReturnTypeCoercion
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
     * @return PersonModel[]
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory->make(
            new PersonRecord()
        )
            ->withOrder('last_name', 'asc')
            ->withOrder('first_name', 'asc')
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN'
            );
        }

        if (count($fetchModel->firstNames) > 0) {
            $query = $query->withWhere(
                'first_name',
                $fetchModel->firstNames,
                'IN'
            );
        }

        if (count($fetchModel->lastNames) > 0) {
            $query = $query->withWhere(
                'last_name',
                $fetchModel->lastNames,
                'IN'
            );
        }

        if (count($fetchModel->emails) > 0) {
            $query = $query->withWhere(
                'email',
                $fetchModel->emails,
                'IN'
            );
        }

        if (count($fetchModel->slugs) > 0) {
            $query = $query->withWhere(
                'slug',
                $fetchModel->slugs,
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
