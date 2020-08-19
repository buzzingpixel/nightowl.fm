<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Persistence\RecordQueryFactory;
use App\Persistence\Shows\ShowRecord;

class ValidateUniqueShowSlug
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
        $query = $this->recordQueryFactory->make(new ShowRecord())
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
