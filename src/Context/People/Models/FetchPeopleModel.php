<?php

declare(strict_types=1);

namespace App\Context\People\Models;

class FetchPeopleModel
{
    public ?int $limit = null;
    public int $offset = 0;
}
