<?php

declare(strict_types=1);

namespace App\Context\People;

use App\Context\People\EventListeners\DeletePersonAfterDeleteDeleteProfilePhoto;
use Crell\Tukio\OrderedListenerProvider;

class PeopleRegisterEventListeners
{
    public function register(OrderedListenerProvider $provider): void
    {
        $provider->addSubscriber(
            DeletePersonAfterDeleteDeleteProfilePhoto::class,
            DeletePersonAfterDeleteDeleteProfilePhoto::class,
        );
    }
}
