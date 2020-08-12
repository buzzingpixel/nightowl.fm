<?php

declare(strict_types=1);

namespace App\Context\People;

use App\Context\People\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\Services\DeletePerson;
use App\Context\People\Services\FetchPeople;
use App\Context\People\Services\GetPersonProfilePhotoUrl;
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

    /**
     * @return PersonModel[]
     */
    public function fetchPeople(?FetchModel $fetchPeopleModel = null): array
    {
        $service = $this->di->get(FetchPeople::class);

        assert($service instanceof FetchPeople);

        return $service->fetch($fetchPeopleModel);
    }

    public function fetchPerson(
        ?FetchModel $fetchPeopleModel = null
    ): ?PersonModel {
        $fetchPeopleModel ??= new FetchModel();

        $fetchPeopleModel->limit = 1;

        return $this->fetchPeople($fetchPeopleModel)[0] ?? null;
    }

    /**
     * @param mixed[] $opt
     */
    public function getPersonProfilePhotoUrl(
        PersonModel $person,
        array $opt = []
    ): string {
        $service = $this->di->get(GetPersonProfilePhotoUrl::class);

        assert($service instanceof GetPersonProfilePhotoUrl);

        return $service->get($person, $opt);
    }

    public function deletePerson(PersonModel $person): Payload
    {
        $service = $this->di->get(DeletePerson::class);

        assert($service instanceof DeletePerson);

        return $service->delete($person);
    }
}
