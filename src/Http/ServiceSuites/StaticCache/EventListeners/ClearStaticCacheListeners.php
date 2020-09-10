<?php

declare(strict_types=1);

namespace App\Http\ServiceSuites\StaticCache\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeAfterDelete;
use App\Context\Episodes\Events\SaveEpisodeAfterSave;
use App\Context\Pages\Events\DeletePageAfterDelete;
use App\Context\Pages\Events\SavePageAfterSave;
use App\Context\People\Events\DeletePersonAfterDelete;
use App\Context\People\Events\SavePersonAfterSave;
use App\Context\Series\Events\SaveSeriesAfterSave;
use App\Context\Shows\Events\DeleteShowAfterDelete;
use App\Context\Shows\Events\SaveShowAfterSave;
use App\Http\ServiceSuites\StaticCache\StaticCacheApi;

class ClearStaticCacheListeners
{
    private StaticCacheApi $staticCacheApi;

    public function __construct(StaticCacheApi $staticCacheApi)
    {
        $this->staticCacheApi = $staticCacheApi;
    }

    public function onAfterSaveEpisode(SaveEpisodeAfterSave $afterSave): void
    {
        if (! $afterSave->episode->isPublished) {
            return;
        }

        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterDeleteEpisode(DeleteEpisodeAfterDelete $afterDelete): void
    {
        if (! $afterDelete->episode->isPublished) {
            return;
        }

        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterSavePerson(SavePersonAfterSave $afterSave): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterDeletePerson(DeletePersonAfterDelete $afterDelete): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterSaveSeries(SaveSeriesAfterSave $afterSave): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterDeletePage(DeletePageAfterDelete $afterDelete): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterSavePage(SavePageAfterSave $afterSave): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterSaveShow(SaveShowAfterSave $afterSave): void
    {
        $this->staticCacheApi->clearStaticCache();
    }

    public function onAfterDeleteShow(DeleteShowAfterDelete $afterDelete): void
    {
        $this->staticCacheApi->clearStaticCache();
    }
}
