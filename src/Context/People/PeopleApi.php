<?php

declare(strict_types=1);

namespace App\Context\People;

use App\Context\People\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\Services\DeletePerson;
use App\Context\People\Services\FetchPeople;
use App\Context\People\Services\GetPersonProfilePhotoUrl;
use App\Context\People\Services\SavePerson;
use App\Context\People\Services\ValidateUniquePersonSlug;
use App\Context\People\Transformers\PersonModelsToSelectArray;
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

    /**
     * @param PersonModel[] $models
     *
     * @return mixed[]
     */
    public function transformPersonModelsToSelectArray(array $models): array
    {
        $service = $this->di->get(PersonModelsToSelectArray::class);

        assert($service instanceof PersonModelsToSelectArray);

        return $service->transform($models);
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

    public function validateUnqiePersonSlug(
        string $proposedSlug,
        ?string $existingId = null
    ): bool {
        $service = $this->di->get(ValidateUniquePersonSlug::class);

        assert($service instanceof ValidateUniquePersonSlug);

        return $service->validate(
            $proposedSlug,
            $existingId,
        );
    }
}
