<?php

declare(strict_types=1);

namespace App\Context\Settings\Models;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $keys = [];
}
