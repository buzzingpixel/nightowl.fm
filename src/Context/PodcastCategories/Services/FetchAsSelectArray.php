<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Services;

use App\Context\PodcastCategories\Models\FetchModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;

class FetchAsSelectArray
{
    private FetchPodcastCategories $fetchPodcastCategories;

    private bool $alreadyPopulated = false;

    /** @var mixed[] */
    private array $selectArray = [];

    public function __construct(FetchPodcastCategories $fetchPodcastCategories)
    {
        $this->fetchPodcastCategories = $fetchPodcastCategories;
    }

    /**
     * @return mixed[]
     */
    public function fetch(): array
    {
        if (! $this->alreadyPopulated) {
            $this->populate();

            $this->alreadyPopulated = true;
        }

        return $this->selectArray;
    }

    private function populate(): void
    {
        $fetchModel = new FetchModel();

        $fetchModel->hierarchical = true;

        $categories = $this->fetchPodcastCategories->fetch(
            $fetchModel
        );

        foreach ($categories as $category) {
            $this->populateItem($category);
        }
    }

    private function populateItem(PodcastCategoryModel $category): void
    {
        $this->selectArray[] = [
            'value' => $category->id,
            'label' => $category->getParentChainWithSelfAsPath(),
        ];

        foreach ($category->children as $child) {
            $this->populateItem($child);
        }
    }
}
