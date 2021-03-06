<?php

declare(strict_types=1);

namespace App\Context\Episodes\Models;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\ShowModel;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $showIds = [];

    /** @var string[] */
    public array $notShowIds = [];

    /** @var ShowModel[] */
    public array $shows = [];

    /** @var ShowModel[] */
    public array $notShows = [];

    /** @var string[] */
    public array $titles = [];

    /** @var string[] */
    public array $statuses = [];

    public string $status = '';

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

    public string $search = '';

    /** @var KeywordModel[] */
    public array $keywords = [];

    /** @var PersonModel[] */
    public array $hosts = [];

    /** @var PersonModel[] */
    public array $guests = [];

    /** @var SeriesModel[] */
    public array $series = [];
}
