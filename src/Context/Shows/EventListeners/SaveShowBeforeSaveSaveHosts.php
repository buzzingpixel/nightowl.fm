<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\SaveShowBeforeSave;
use App\Context\Shows\Services\Internal\SaveShowHosts;

class SaveShowBeforeSaveSaveHosts
{
    private SaveShowHosts $saveShowHosts;

    public function __construct(SaveShowHosts $saveShowHosts)
    {
        $this->saveShowHosts = $saveShowHosts;
    }

    public function onBeforeSave(SaveShowBeforeSave $beforeSave): void
    {
        $this->saveShowHosts->save($beforeSave->show);
    }
}
