<?php

declare(strict_types=1);

namespace App\Context\Keywords\Models;

use Cocur\Slugify\Slugify;
use Config\General;

use function implode;

class KeywordModel
{
    public string $id = '';

    public string $keyword = '';

    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->keyword);
    }

    public function getPublicUrl(): string
    {
        return implode('/', [
            General::$siteUrl,
            'topics',
            $this->getSlug(),
        ]);
    }
}
