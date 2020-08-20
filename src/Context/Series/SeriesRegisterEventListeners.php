<?php

declare(strict_types=1);

namespace App\Context\Series;

use App\Context\Series\EventListeners\SaveSeriesBeforeSaveValidateUniqueSlug;
use Crell\Tukio\OrderedListenerProvider;

class SeriesRegisterEventListeners
{
    public function register(OrderedListenerProvider $provider): void
    {
        $provider->addSubscriber(
            SaveSeriesBeforeSaveValidateUniqueSlug::class,
            SaveSeriesBeforeSaveValidateUniqueSlug::class,
        );
    }
}
