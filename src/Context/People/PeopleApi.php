<?php

declare(strict_types=1);

namespace App\Context\People;

use App\Context\People\Models\PersonModel;
use App\Context\People\Services\SavePerson;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class PeopleApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function savePerson(PersonModel $person): Payload
    {
        $service = $this->di->get(SavePerson::class);

        assert($service instanceof SavePerson);

        return $service->save($person);
    }
}
