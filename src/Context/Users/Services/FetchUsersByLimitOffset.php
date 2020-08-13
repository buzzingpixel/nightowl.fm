<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Transformers\TransformUserRecordToUserModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Users\UserRecord;

use function array_map;

class FetchUsersByLimitOffset
{
    private RecordQueryFactory $recordQueryFactory;
    private TransformUserRecordToUserModel $transformUserRecordToUserModel;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        TransformUserRecordToUserModel $transformUserRecordToUserModel
    ) {
        $this->recordQueryFactory             = $recordQueryFactory;
        $this->transformUserRecordToUserModel = $transformUserRecordToUserModel;
    }

    /**
     * @return UserModel[]
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function __invoke(?int $limit = null, int $offset = 0): array
    {
        $records = ($this->recordQueryFactory)(
            new UserRecord()
        )
            ->withOrder('email_address', 'asc')
            ->withLimit($limit)
            ->withOffset($offset)
            ->all();

        /** @psalm-suppress MixedReturnTypeCoercion */
        return array_map(
            $this->transformUserRecordToUserModel,
            $records
        );
    }
}
