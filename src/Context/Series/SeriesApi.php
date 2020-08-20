<?php

declare(strict_types=1);

namespace App\Context\Series;

use App\Context\Series\Models\FetchModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Series\Services\FetchSeries;
use App\Context\Series\Services\SaveSeries;
use App\Context\Series\Services\ValidateUniqueSeriesSlug;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class SeriesApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveSeries(SeriesModel $series): Payload
    {
        $service = $this->di->get(SaveSeries::class);

        assert($service instanceof SaveSeries);

        return $service->save($series);
    }

    /**
     * @return SeriesModel[]
     */
    public function fetchSeries(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchSeries::class);

        assert($service instanceof FetchSeries);

        return $service->fetch($fetchModel);
    }

    public function fetchOneSeries(?FetchModel $fetchModel = null): ?SeriesModel
    {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchSeries($fetchModel)[0] ?? null;
    }

    public function validateUniqueSeriesSlug(
        string $proposedSlug,
        string $showId,
        ?string $existingId = null
    ): bool {
        $service = $this->di->get(ValidateUniqueSeriesSlug::class);

        assert($service instanceof ValidateUniqueSeriesSlug);

        return $service->validate(
            $proposedSlug,
            $showId,
            $existingId
        );
    }
}
