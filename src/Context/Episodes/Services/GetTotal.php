<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\Models\FetchModel;
use App\Context\Episodes\Services\Internal\BuildFetchQuery;

class GetTotal
{
    private BuildFetchQuery $buildFetchQuery;

    public function __construct(BuildFetchQuery $buildFetchQuery)
    {
        $this->buildFetchQuery = $buildFetchQuery;
    }

    public function get(?FetchModel $fetchModel = null): int
    {
        $fetchModel ??= new FetchModel();

        $query = $this->buildFetchQuery->build($fetchModel);

        return $query->count();
    }
}
