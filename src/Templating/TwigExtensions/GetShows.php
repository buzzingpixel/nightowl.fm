<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetShows extends AbstractExtension
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
            'getShows',
            [$this, 'getShows']
        );
    }

    /** @var ShowModel[]|null */
    private ?array $shows = null;

    /**
     * @return ShowModel[]
     */
    public function getShows(): array
    {
        if ($this->shows === null) {
            $this->fetchShows();
        }

        /** @phpstan-ignore-next-line */
        return $this->shows;
    }

    private function fetchShows(): void
    {
        $fetchModel = new FetchModel();

        $fetchModel->statuses = [ShowConstants::SHOW_STATUS_LIVE];

        $this->shows = $this->showApi->fetchShows($fetchModel);
    }
}
