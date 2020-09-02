<?php

declare(strict_types=1);

namespace App\Context\Episodes;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Episodes\Services\DeleteEpisode;
use App\Context\Episodes\Services\FetchEpisodes;
use App\Context\Episodes\Services\GetTotalSecondsPodcasted;
use App\Context\Episodes\Services\PublishPendingEpisodes;
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

    public function publishPendingEpisodes(): void
    {
        $service = $this->di->get(PublishPendingEpisodes::class);

        assert($service instanceof PublishPendingEpisodes);

        $service->run();
    }

    public function deleteEpisode(EpisodeModel $episode): Payload
    {
        $service = $this->di->get(DeleteEpisode::class);

        assert($service instanceof DeleteEpisode);

        return $service->delete($episode);
    }

    public function getTotalSecondsPodcasted(): int
    {
        $service = $this->di->get(GetTotalSecondsPodcasted::class);

        assert($service instanceof GetTotalSecondsPodcasted);

        return $service->get();
    }

    public function getTotalMinutesPodcasted(): int
    {
        return (int) ($this->getTotalSecondsPodcasted() / 60);
    }
}
