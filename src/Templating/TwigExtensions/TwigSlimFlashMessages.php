<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use Slim\Flash\Messages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigSlimFlashMessages extends AbstractExtension
{
    protected Messages $flash;

    /**
     * @param Messages $flash the Flash messages service provider
     */
    public function __construct(Messages $flash)
    {
        $this->flash = $flash;
    }

    /**
     * Callback for twig.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, 'getMessages']),
        ];
    }

    /**
     * Returns Flash messages; If key is provided then returns messages
     * for that key.
     *
     * @return mixed
     */
    public function getMessages(?string $key = null)
    {
        if ($key !== null) {
            return $this->flash->getMessage($key);
        }

        return $this->flash->getMessages();
    }
}
