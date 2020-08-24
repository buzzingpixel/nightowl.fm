<?php

declare(strict_types=1);

namespace App\Context\Episodes;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Services\SaveEpisode;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class EpisodeApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveEpisode(EpisodeModel $episode): Payload
    {
        $service = $this->di->get(SaveEpisode::class);

        assert($service instanceof SaveEpisode);

        return $service->save($episode);
    }
}
