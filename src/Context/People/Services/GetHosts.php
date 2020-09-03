<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Models\PersonModel;
use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use Safe\Exceptions\ArrayException;

use function array_values;
use function Safe\ksort;

use const SORT_NATURAL;

class GetHosts
{
    private ShowApi $showApi;

    /** @var PersonModel[]|null */
    private ?array $hosts = null;

    public function __construct(ShowApi $showApi)
    {
        $this->showApi = $showApi;
    }

    /**
     * @return PersonModel[]
     *
     * @throws ArrayException
     *
     * @psalm-suppress MixedReturnTypeCoercion
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function get(): array
    {
        if ($this->hosts !== null) {
            return $this->hosts;
        }

        $fetchModel = new FetchModel();

        $fetchModel->notStatuses[] = ShowConstants::SHOW_STATUS_HIDDEN;

        $shows      = $this->showApi->fetchShows($fetchModel);
        $keyedHosts = [];

        foreach ($shows as $show) {
            foreach ($show->hosts as $person) {
                $keyedHosts[$person->getFullName()] = $person;
            }
        }

        ksort($keyedHosts, SORT_NATURAL);

        $this->hosts = array_values($keyedHosts);

        return $this->hosts;
    }
}
