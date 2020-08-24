<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Keywords\KeywordsApi;

use function array_walk;

class SaveEpisodeBeforeSaveSaveKeywords
{
    private KeywordsApi $keywordsApi;

    public function __construct(KeywordsApi $keywordsApi)
    {
        $this->keywordsApi = $keywordsApi;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $keywords = $beforeSave->episode->keywords;

        array_walk(
            $keywords,
            [$this->keywordsApi, 'saveKeyword']
        );
    }
}
