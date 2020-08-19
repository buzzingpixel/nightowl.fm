<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Persistence\People\PersonRecord;
use App\Persistence\RecordQueryFactory;

class ValidateUniquePersonSlug
{
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(
        RecordQueryFactory $recordQueryFactory
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
    }

    public function validate(
        string $proposedSlug,
        ?string $existingId = null
    ): bool {
        $query = $this->recordQueryFactory
            ->make(new PersonRecord())
            ->withWhere('slug', $proposedSlug);

        if ($existingId !== null && $existingId !== '') {
            $query = $query->withWhere(
                'id',
                $existingId,
                '!='
            );
        }

        return $query->count() < 1;
    }
}
