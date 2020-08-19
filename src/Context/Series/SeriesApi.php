<?php

declare(strict_types=1);

namespace App\Context\Series;

use App\Context\Series\Models\SeriesModel;
use App\Context\Series\Services\SaveSeries;
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
}
