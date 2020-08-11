<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Models\FetchPeopleModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\Transformers\RecordToModel;
use App\Persistence\People\PersonRecord;
use App\Persistence\RecordQueryFactory;

use function array_map;

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
    public function fetch(?FetchPeopleModel $fetchPeopleModel = null): array
    {
        $fetchPeopleModel ??= new FetchPeopleModel();

        $records = $this->recordQueryFactory->make(
            new PersonRecord()
        )
            ->withOrder('last_name', 'asc')
            ->withOrder('first_name', 'asc')
            ->withLimit($fetchPeopleModel->limit)
            ->withOffset($fetchPeopleModel->offset)
            ->all();

        /** @psalm-suppress MixedReturnTypeCoercion */
        return array_map(
            $this->recordToModel,
            $records
        );
    }
}
