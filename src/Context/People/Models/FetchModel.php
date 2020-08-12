<?php

declare(strict_types=1);

namespace App\Context\People\Models;

class FetchModel
{
    public ?int $limit = null;

    public int $offset = 0;

    /** @var string[] */
    public array $ids = [];

    /** @var string[] */
    public array $firstNames = [];

    /** @var string[] */
    public array $lastNames = [];

    /** @var string[] */
    public array $emails = [];

    /** @var string[] */
    public array $slugs = [];
}
