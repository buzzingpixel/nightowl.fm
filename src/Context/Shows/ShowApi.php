<?php

declare(strict_types=1);

namespace App\Context\Shows;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Services\FetchShows;
use App\Context\Shows\Services\SaveShow;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class ShowApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveShow(ShowModel $show): Payload
    {
        $service = $this->di->get(SaveShow::class);

        assert($service instanceof SaveShow);

        return $service->save($show);
    }

    /**
     * @return ShowModel[]
     */
    public function fetchShows(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchShows::class);

        assert($service instanceof FetchShows);

        return $service->fetch($fetchModel);
    }

    public function fetchShow(?FetchModel $fetchModel = null): ?ShowModel
    {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchShows($fetchModel)[0] ?? null;
    }
}
