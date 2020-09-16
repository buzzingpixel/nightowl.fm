<?php

declare(strict_types=1);

namespace App\Context\Shows;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Services\DeleteShow;
use App\Context\Shows\Services\FetchShows;
use App\Context\Shows\Services\GenerateShowRssFeed;
use App\Context\Shows\Services\GetShowArtworkUrl;
use App\Context\Shows\Services\SaveShow;
use App\Context\Shows\Services\ValidateUniqueShowSlug;
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

    public function validateUniqueShowSlug(
        string $proposedSlug,
        ?string $existingId = null
    ): bool {
        $service = $this->di->get(ValidateUniqueShowSlug::class);

        assert($service instanceof ValidateUniqueShowSlug);

        return $service->validate(
            $proposedSlug,
            $existingId,
        );
    }

    public function deleteShow(ShowModel $show): Payload
    {
        $service = $this->di->get(DeleteShow::class);

        assert($service instanceof DeleteShow);

        return $service->delete($show);
    }

    /**
     * @param mixed[] $opt
     */
    public function getShowArtworkUrl(ShowModel $show, array $opt = []): string
    {
        $service = $this->di->get(GetShowArtworkUrl::class);

        assert($service instanceof GetShowArtworkUrl);

        return $service->get($show, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    public function getShowArtworkUrlPublic(
        ShowModel $show,
        array $opt = []
    ): string {
        return $this->getShowArtworkUrl(
            $show,
            $opt
        );
    }

    public function generateShowRssFeed(ShowModel $show): string
    {
        $service = $this->di->get(GenerateShowRssFeed::class);

        assert($service instanceof GenerateShowRssFeed);

        return $service->generate($show);
    }
}
