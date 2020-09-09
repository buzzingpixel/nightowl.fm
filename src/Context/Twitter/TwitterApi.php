<?php

declare(strict_types=1);

namespace App\Context\Twitter;

use App\Context\Twitter\Models\TwitterSettingsModel;
use App\Context\Twitter\Services\FetchTwitterSettings;
use App\Context\Twitter\Services\SaveTwitterSettings;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class TwitterApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveTwitterSettings(TwitterSettingsModel $model): Payload
    {
        $service = $this->di->get(SaveTwitterSettings::class);

        assert($service instanceof SaveTwitterSettings);

        return $service->save($model);
    }

    public function fetchTwitterSettings(): TwitterSettingsModel
    {
        $service = $this->di->get(FetchTwitterSettings::class);

        assert($service instanceof FetchTwitterSettings);

        return $service->fetch();
    }
}
