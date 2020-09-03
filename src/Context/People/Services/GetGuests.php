<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\FetchModel;
use App\Context\People\Models\PersonModel;
use Safe\Exceptions\ArrayException;

use function array_values;
use function in_array;
use function Safe\ksort;

use const SORT_NATURAL;

class GetGuests
{
    private GetHosts $getHosts;
    private EpisodeApi $episodeApi;

    /** @var PersonModel[]|null */
    private ?array $guests = null;

    public function __construct(
        GetHosts $getHosts,
        EpisodeApi $episodeApi
    ) {
        $this->getHosts   = $getHosts;
        $this->episodeApi = $episodeApi;
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
        if ($this->guests !== null) {
            return $this->guests;
        }

        $hostIds = [];

        foreach ($this->getHosts->get() as $host) {
            $hostIds[$host->id] = $host->id;
        }

        $fetchModel = new FetchModel();

        $fetchModel->statuses = [EpisodeConstants::EPISODE_STATUS_LIVE];

        $fetchModel->excludeEpisodesFromHiddenShows = true;

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        $keyedGuests = [];

        foreach ($episodes as $episode) {
            foreach ($episode->guests as $person) {
                if (
                    in_array(
                        $person->id,
                        $hostIds,
                        true
                    )
                ) {
                    continue;
                }

                $keyedGuests[$person->getFullName()] = $person;
            }
        }

        ksort($keyedGuests, SORT_NATURAL);

        $this->guests = array_values($keyedGuests);

        return $this->guests;
    }
}
