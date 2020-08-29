<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Events;

use App\Context\Events\StoppableEvent;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;

class SavePodcastCategoryBeforeSave extends StoppableEvent
{
    public PodcastCategoryModel $podcastCategory;
    public bool $isValid = true;

    public function __construct(PodcastCategoryModel $podcastCategory)
    {
        $this->podcastCategory = $podcastCategory;
    }
}
