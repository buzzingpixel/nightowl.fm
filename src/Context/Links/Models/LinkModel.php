<?php

declare(strict_types=1);

namespace App\Context\Links\Models;

class LinkModel
{
    public string $title = '';

    public string $url = '';

    public function __construct(string $title, string $url)
    {
        $this->title = $title;

        $this->url = $url;
    }
}
