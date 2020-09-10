<?php

declare(strict_types=1);

namespace App\Context\Shows\Models;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $notIds = [];

    /** @var string[] */
    public array $titles = [];

    /** @var string[] */
    public array $slugs = [];

    /** @var string[] */
    public array $statuses = [];

    /** @var string[] */
    public array $notStatuses = [];

    public ?bool $explicit = null;

    /** @var KeywordModel[] */
    public array $keywords = [];

    /** @var PersonModel[] */
    public array $hosts = [];

    /** @var PersonModel[] */
    public array $guests = [];
}
