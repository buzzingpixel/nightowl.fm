<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Episodes\EpisodeApi;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetTotalSecondsPodcasted extends AbstractExtension
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'getTotalSecondsPodcasted',
                [$this, 'getTotalSecondsPodcasted']
            ),
            new TwigFunction(
                'getTotalMinutesPodcasted',
                [$this, 'getTotalMinutesPodcasted']
            ),
        ];
    }

    public function getTotalSecondsPodcasted(): int
    {
        return $this->episodeApi->getTotalSecondsPodcasted();
    }

    public function getTotalMinutesPodcasted(): int
    {
        return $this->episodeApi->getTotalMinutesPodcasted();
    }
}
