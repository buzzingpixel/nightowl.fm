<?php

declare(strict_types=1);

namespace App\Context\Episodes;

use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveEpisodeKeywords;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveGuests;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveHosts;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveKeywords;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveNewFile;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSaveSeries;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSetEpisodePublishedState;
use App\Context\Episodes\EventListeners\SaveEpisodeBeforeSaveSetOrderAndNumber;
use Crell\Tukio\OrderedListenerProvider;

class EpisodesRegisterEventListeners
{
    public function register(OrderedListenerProvider $provider): void
    {
        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSetOrderAndNumber::class,
            SaveEpisodeBeforeSaveSetOrderAndNumber::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveNewFile::class,
            SaveEpisodeBeforeSaveSaveNewFile::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveKeywords::class,
            SaveEpisodeBeforeSaveSaveKeywords::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveEpisodeKeywords::class,
            SaveEpisodeBeforeSaveSaveEpisodeKeywords::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveHosts::class,
            SaveEpisodeBeforeSaveSaveHosts::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveGuests::class,
            SaveEpisodeBeforeSaveSaveGuests::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSaveSeries::class,
            SaveEpisodeBeforeSaveSaveSeries::class,
        );

        $provider->addSubscriber(
            SaveEpisodeBeforeSaveSetEpisodePublishedState::class,
            SaveEpisodeBeforeSaveSetEpisodePublishedState::class,
        );
    }
}
