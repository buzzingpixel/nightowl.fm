<?php

declare(strict_types=1);

namespace App\Context\Series\Models;

use App\Context\Shows\Models\ShowModel;
use Config\General;

use function implode;

class SeriesModel
{
    public string $id = '';

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    public ShowModel $show;

    public function getPublicUrl(): string
    {
        return implode('/', [
            General::$siteUrl,
            $this->show->slug,
            'series',
            $this->slug,
        ]);
    }

    public function getTitleWithShowTitle(): string
    {
        return implode(' ', [
            $this->show->title,
            'Series:',
            $this->title,
        ]);
    }
}
