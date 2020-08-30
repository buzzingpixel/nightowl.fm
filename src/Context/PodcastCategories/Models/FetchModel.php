<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Models;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    public bool $hierarchical = false;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $parentIds = [];

    /** @var string[] */
    public array $names = [];
}
