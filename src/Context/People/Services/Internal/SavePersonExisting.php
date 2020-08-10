<?php

declare(strict_types=1);

namespace App\Context\People\Services\Internal;

use App\Context\People\Models\PersonModel;
use App\Payload\Payload;

use function dd;

class SavePersonExisting
{
    public function save(PersonModel $person): Payload
    {
        // TODO
        dd($person);
    }
}
