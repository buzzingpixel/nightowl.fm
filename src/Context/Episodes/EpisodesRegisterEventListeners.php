<?php

declare(strict_types=1);

namespace App\Context\Episodes;

use App\Context\Episodes\EventListeners\DeleteEpisodeBeforeDeleteDeleteFile;
use App\Context\Episodes\EventListeners\DeleteEpisodeBeforeDeleteDeleteGuests;
use App\Context\Episodes\EventListeners\DeleteEpisodeBeforeDeleteDeleteHosts;
use App\Context\Episodes\EventListeners\DeleteEpisodeBeforeDeleteDeleteKeywords;
use App\Context\Episodes\EventListeners\DeleteEpisodeBeforeDeleteDeleteSeries;
use App\Context\Episodes\EventListeners\DeleteShowAfterDeleteQueueEpisodesDelete;
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
            SaveEpisodeBeforeSaveSetEpisodePublishedState::class,
            SaveEpisodeBeforeSaveSetEpisodePublishedState::class,
        );

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
            DeleteEpisodeBeforeDeleteDeleteGuests::class,
            DeleteEpisodeBeforeDeleteDeleteGuests::class,
        );

        $provider->addSubscriber(
            DeleteEpisodeBeforeDeleteDeleteHosts::class,
            DeleteEpisodeBeforeDeleteDeleteHosts::class,
        );

        $provider->addSubscriber(
            DeleteEpisodeBeforeDeleteDeleteKeywords::class,
            DeleteEpisodeBeforeDeleteDeleteKeywords::class,
        );

        $provider->addSubscriber(
            DeleteEpisodeBeforeDeleteDeleteSeries::class,
            DeleteEpisodeBeforeDeleteDeleteSeries::class,
        );

        $provider->addSubscriber(
            DeleteEpisodeBeforeDeleteDeleteFile::class,
            DeleteEpisodeBeforeDeleteDeleteFile::class,
        );

        $provider->addSubscriber(
            DeleteShowAfterDeleteQueueEpisodesDelete::class,
            DeleteShowAfterDeleteQueueEpisodesDelete::class,
        );
    }
}
