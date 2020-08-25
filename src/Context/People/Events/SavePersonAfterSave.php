<?php

declare(strict_types=1);

namespace App\Context\People\Events;

use App\Context\Events\StoppableEvent;
use App\Context\People\Models\PersonModel;

class SavePersonAfterSave extends StoppableEvent
{
    public PersonModel $person;
    public bool $isValid = true;

    public function __construct(PersonModel $person)
    {
        $this->person = $person;
    }
}
