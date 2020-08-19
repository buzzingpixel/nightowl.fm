<?php

declare(strict_types=1);

namespace App\Context\Shows;

use App\Context\Keywords\EventListeners\SaveShowBeforeSaveSaveKeywords;
use App\Context\Shows\EventListeners\DeleteShowAfterDeleteDeleteShowArtwork;
use App\Context\Shows\EventListeners\DeleteShowBeforeDeleteDeleteHosts;
use App\Context\Shows\EventListeners\DeleteShowBeforeDeleteDeleteKeywords;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveHosts;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveNewArtwork;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveShowKeywords;
use Crell\Tukio\OrderedListenerProvider;

class ShowsRegisterEventListeners
{
    public function register(OrderedListenerProvider $provider): void
    {
        $provider->addSubscriber(
            SaveShowBeforeSaveSaveKeywords::class,
            SaveShowBeforeSaveSaveKeywords::class,
        );

        $provider->addSubscriber(
            SaveShowBeforeSaveSaveNewArtwork::class,
            SaveShowBeforeSaveSaveNewArtwork::class,
        );

        $provider->addSubscriber(
            SaveShowBeforeSaveSaveShowKeywords::class,
            SaveShowBeforeSaveSaveShowKeywords::class,
        );

        $provider->addSubscriber(
            SaveShowBeforeSaveSaveHosts::class,
            SaveShowBeforeSaveSaveHosts::class,
        );

        $provider->addSubscriber(
            DeleteShowBeforeDeleteDeleteKeywords::class,
            DeleteShowBeforeDeleteDeleteKeywords::class,
        );

        $provider->addSubscriber(
            DeleteShowBeforeDeleteDeleteHosts::class,
            DeleteShowBeforeDeleteDeleteHosts::class,
        );

        $provider->addSubscriber(
            DeleteShowAfterDeleteDeleteShowArtwork::class,
            DeleteShowAfterDeleteDeleteShowArtwork::class,
        );
    }
}
