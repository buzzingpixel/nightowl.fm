<?php

declare(strict_types=1);

namespace App\Context\Episodes\Models;

use App\Context\Shows\Models\ShowModel;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $showIds = [];

    /** @var ShowModel[] */
    public array $shows = [];

    /** @var string[] */
    public array $titles = [];

    /** @var string[] */
    public array $statuses = [];

    /** @var string[] */
    public array $episodeTypes = [];

    public ?bool $isExplicit = null;

    public ?bool $isPublished = null;

    /** @var int[] */
    public array $episodeNumbers = [];

    public bool $pastPublishedAt = false;

    public bool $orderByPublishedAt = false;

    public bool $orderByRandom = false;

    public bool $excludeEpisodesFromHiddenShows = false;
}
