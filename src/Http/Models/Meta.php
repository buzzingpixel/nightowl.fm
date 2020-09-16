<?php

declare(strict_types=1);

namespace App\Http\Models;

use Config\General;

class Meta
{
    public function __construct()
    {
        $this->shareImage = General::$siteUrl . '/share.png';
    }

    public bool $noIndex = false;

    public string $title = '';

    public string $description = '';

    public string $ogType = 'website';

    public string $twitterCardType = 'summary';

    public string $shareImage;
}
