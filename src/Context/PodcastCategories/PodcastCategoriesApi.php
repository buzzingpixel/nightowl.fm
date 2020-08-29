<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
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
}
