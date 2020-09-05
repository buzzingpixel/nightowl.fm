<?php

declare(strict_types=1);

namespace App\Context\Keywords\Models;

use Config\General;

use function implode;

class KeywordModel
{
    public string $id = '';

    public string $keyword = '';

    public string $slug = '';

    public function getPublicUrl(): string
    {
        return implode('/', [
            General::$siteUrl,
            'topics',
            $this->slug,
        ]);
    }
}
