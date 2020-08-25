<?php

declare(strict_types=1);

namespace App\Context\Episodes;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Episodes\Services\FetchEpisodes;
use App\Context\Episodes\Services\SaveEpisode;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class EpisodeApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveEpisode(EpisodeModel $episode): Payload
    {
        $service = $this->di->get(SaveEpisode::class);

        assert($service instanceof SaveEpisode);

        return $service->save($episode);
    }

    /**
     * @return EpisodeModel[]
     */
    public function fetchEpisodes(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchEpisodes::class);

        assert($service instanceof FetchEpisodes);

        return $service->fetch($fetchModel);
    }

    public function fetchEpisode(?FetchModel $fetchModel = null): ?EpisodeModel
    {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchEpisodes($fetchModel)[0] ?? null;
    }
}
