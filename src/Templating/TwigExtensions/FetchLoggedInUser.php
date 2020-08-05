<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Users\Models\LoggedInUser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FetchLoggedInUser extends AbstractExtension
{
    private LoggedInUser $loggedInUser;

    public function __construct(LoggedInUser $loggedInUser)
    {
        $this->loggedInUser = $loggedInUser;
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

    public function fetchLoggedInUser(): ?LoggedInUser
    {
        return $this->loggedInUser;
    }
}
