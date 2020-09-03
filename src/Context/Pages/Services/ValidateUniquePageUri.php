<?php

declare(strict_types=1);

namespace App\Context\Pages\Services;

use App\Persistence\Pages\PageRecord;
use App\Persistence\RecordQueryFactory;

class ValidateUniquePageUri
{
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(
        RecordQueryFactory $recordQueryFactory
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
    }

    public function validate(
        string $proposedUri,
        ?string $existingId = null
    ): bool {
        $query = $this->recordQueryFactory
            ->make(new PageRecord())
            ->withWhere('uri', $proposedUri);

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
