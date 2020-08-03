<?php

declare(strict_types=1);

namespace App\Http\Models;

class Meta
{
    public bool $noIndex = false;

    public string $title = '';

    public string $description = '';

    public string $ogType = 'website';

    public string $twitterCardType = 'summary';

    public string $shareImage = '/share.png';
}
