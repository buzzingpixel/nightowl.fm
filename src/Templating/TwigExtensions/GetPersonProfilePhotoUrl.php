<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetPersonProfilePhotoUrl extends AbstractExtension
{
    private PeopleApi $peopleApi;

    public function __construct(PeopleApi $peopleApi)
    {
        $this->peopleApi = $peopleApi;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [$this->getFunction()];
    }

    private function getFunction(): TwigFunction
    {
        return new TwigFunction(
            'getPersonProfilePhotoUrl',
            [$this, 'get']
        );
    }

    /**
     * @param mixed[] $opt
     */
    public function get(PersonModel $person, array $opt = []): string
    {
        return $this->peopleApi->getPersonProfilePhotoUrl(
            $person,
            $opt,
        );
    }
}
