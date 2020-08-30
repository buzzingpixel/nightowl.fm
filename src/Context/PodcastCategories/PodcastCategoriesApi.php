<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories;

use App\Context\PodcastCategories\Models\FetchModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\Services\FetchAsSelectArray;
use App\Context\PodcastCategories\Services\FetchPodcastCategories;
use App\Context\PodcastCategories\Services\SavePodcastCategory;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class PodcastCategoriesApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function savePodcastCategory(PodcastCategoryModel $model): Payload
    {
        $service = $this->di->get(SavePodcastCategory::class);

        assert($service instanceof SavePodcastCategory);

        return $service->save($model);
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function fetchCategories(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchPodcastCategories::class);

        assert($service instanceof FetchPodcastCategories);

        return $service->fetch($fetchModel);
    }

    public function fetchCategory(
        ?FetchModel $fetchModel = null
    ): ?PodcastCategoryModel {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchCategories($fetchModel)[0] ?? null;
    }

    /**
     * @return mixed[]
     */
    public function fetchCategoriesAsSelectArray(): array
    {
        $service = $this->di->get(FetchAsSelectArray::class);

        assert($service instanceof FetchAsSelectArray);

        return $service->fetch();
    }
}
