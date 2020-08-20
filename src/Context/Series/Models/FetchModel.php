<?php

declare(strict_types=1);

namespace App\Context\Series\Models;

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
    public array $slugs = [];
}
