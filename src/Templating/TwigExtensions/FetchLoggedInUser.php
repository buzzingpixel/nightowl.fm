<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Users\Models\UserModel;
use App\Context\Users\UserApi;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FetchLoggedInUser extends AbstractExtension
{
    private UserApi $userApi;

    public function __construct(UserApi $userApi)
    {
        $this->userApi = $userApi;
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
            'fetchLoggedInUser',
            [$this, 'fetchLoggedInUser']
        );
    }

    public function fetchLoggedInUser(): ?UserModel
    {
        return $this->userApi->fetchLoggedInUser();
    }
}
