<?php

declare(strict_types=1);

namespace Config;

use App\Context\Episodes\EpisodesRegisterEventListeners;
use App\Context\People\PeopleRegisterEventListeners;
use App\Context\Series\SeriesRegisterEventListeners;
use App\Context\Shows\ShowsRegisterEventListeners;
use App\Http\ServiceSuites\StaticCache\StaticCacheRegisterEventListeners;
use Crell\Tukio\OrderedListenerProvider;

class RegisterEventListeners
{
    private OrderedListenerProvider $provider;

    public function __construct(OrderedListenerProvider $provider)
    {
        $this->provider = $provider;
    }

    public function __invoke(): void
    {
        // Method names in subscriber classes must start with `on`. The event
        // will be derived from reflection to see what event it's subscribing to
        // $this->provider->addSubscriber(Test::class, Test::class);
        // public function onBeforeValidate(SaveUserBeforeValidate $beforeValidate) : void
        // {
        //     dd($beforeValidate);
        // }

        $provider = $this->provider;

        (new PeopleRegisterEventListeners())->register($provider);
        (new ShowsRegisterEventListeners())->register($provider);
        (new SeriesRegisterEventListeners())->register($provider);
        (new EpisodesRegisterEventListeners())->register($provider);
        (new StaticCacheRegisterEventListeners())->register($provider);
    }
}
