<?php

declare(strict_types=1);

namespace Config;

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
    }
}
