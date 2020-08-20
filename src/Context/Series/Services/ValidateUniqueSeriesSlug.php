<?php

declare(strict_types=1);

namespace App\Context\Series\Services;

use App\Persistence\RecordQueryFactory;
use App\Persistence\Series\SeriesRecord;

class ValidateUniqueSeriesSlug
{
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(
        RecordQueryFactory $recordQueryFactory
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
    }

    public function validate(
        string $proposedSlug,
        string $showId,
        ?string $existingId = null
    ): bool {
        $query = $this->recordQueryFactory
            ->make(new SeriesRecord())
            ->withWhere('slug', $proposedSlug)
            ->withWhere('show_id', $showId);

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
