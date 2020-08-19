<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetShowArtworkUrl extends AbstractExtension
{
    private ShowApi $showApi;

    public function __construct(ShowApi $showApi)
    {
        $this->showApi = $showApi;
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
            'getShowArtworkUrl',
            [$this, 'get']
        );
    }

    /**
     * @param mixed[] $opt
     */
    public function get(ShowModel $show, array $opt = []): string
    {
        return $this->showApi->getShowArtworkUrl($show, $opt);
    }
}
