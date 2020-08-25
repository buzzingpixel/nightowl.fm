<?php

declare(strict_types=1);

namespace App\Http\ServiceSuites\StaticCache;

use App\Http\ServiceSuites\StaticCache\EventListeners\ClearStaticCacheListeners;
use Crell\Tukio\OrderedListenerProvider;

class StaticCacheRegisterEventListeners
{
    public function register(OrderedListenerProvider $provider): void
    {
        $provider->addSubscriber(
            ClearStaticCacheListeners::class,
            ClearStaticCacheListeners::class,
        );
    }
}
