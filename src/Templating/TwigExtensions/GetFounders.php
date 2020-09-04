<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\People\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetFounders extends AbstractExtension
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
            'getFounders',
            [$this, 'get']
        );
    }

    /**
     * @return PersonModel[]
     */
    public function get(): array
    {
        $fetchModel = new FetchModel();

        $fetchModel->slugs = [
            'tjdraper',
            'joedarnell',
        ];

        return $this->peopleApi->fetchPeople($fetchModel);
    }
}
