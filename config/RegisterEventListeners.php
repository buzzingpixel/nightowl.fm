<?php

declare(strict_types=1);

namespace Config;

use App\Context\Keywords\EventListeners\SaveShowBeforeSaveSaveKeywords;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveHosts;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveNewArtwork;
use App\Context\Shows\EventListeners\SaveShowBeforeSaveSaveShowKeywords;
use Crell\Tukio\OrderedListenerProvider;

class RegisterEventListeners
{
    private OrderedListenerProvider $provider;

    public function __construct(OrderedListenerProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * TODO: Add subscribers here
     */
    public function __invoke(): void
    {
        // Method names in subscriber classes must start with `on`. The event
        // will be derived from reflection to see what event it's subscribing to
        // $this->provider->addSubscriber(Test::class, Test::class);
        // public function onBeforeValidate(SaveUserBeforeValidate $beforeValidate) : void
        // {
        //     dd($beforeValidate);
        // }

        $this->provider->addSubscriber(
            SaveShowBeforeSaveSaveKeywords::class,
            SaveShowBeforeSaveSaveKeywords::class,
        );

        $this->provider->addSubscriber(
            SaveShowBeforeSaveSaveNewArtwork::class,
            SaveShowBeforeSaveSaveNewArtwork::class,
        );

        $this->provider->addSubscriber(
            SaveShowBeforeSaveSaveShowKeywords::class,
            SaveShowBeforeSaveSaveShowKeywords::class,
        );

        $this->provider->addSubscriber(
            SaveShowBeforeSaveSaveHosts::class,
            SaveShowBeforeSaveSaveHosts::class,
        );
    }
}
