<?php

declare(strict_types=1);

namespace App\Context\Keywords\EventListeners;

use App\Context\Keywords\KeywordsApi;
use App\Context\Shows\Events\SaveShowBeforeSave;

use function array_walk;

class SaveShowBeforeSaveSaveKeywords
{
    private KeywordsApi $keywordsApi;

    public function __construct(KeywordsApi $keywordsApi)
    {
        $this->keywordsApi = $keywordsApi;
    }

    public function onBeforeSave(SaveShowBeforeSave $beforeSave): void
    {
        $keywords = $beforeSave->show->keywords;

        array_walk(
            $keywords,
            [$this->keywordsApi, 'saveKeyword']
        );
    }
}
