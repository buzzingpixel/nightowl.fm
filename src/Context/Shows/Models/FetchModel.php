<?php

declare(strict_types=1);

namespace App\Context\Shows\Models;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $titles = [];

    /** @var string[] */
    public array $slugs = [];

    /** @var string[] */
    public array $statuses = [];

    /** @var string[] */
    public array $notStatuses = [];

    public ?bool $explicit = null;

    /** @var string[] */
    public array $keywords = [];
}
