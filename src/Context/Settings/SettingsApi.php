<?php

declare(strict_types=1);

namespace App\Context\Settings;

use App\Context\Settings\Models\FetchModel;
use App\Context\Settings\Models\SettingModel;
use App\Context\Settings\Services\FetchSettings;
use App\Context\Settings\Services\SaveSetting;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class SettingsApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveSetting(SettingModel $model): Payload
    {
        $service = $this->di->get(SaveSetting::class);

        assert($service instanceof SaveSetting);

        return $service->save($model);
    }

    /**
     * @return SettingModel[]
     */
    public function fetchSettings(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchSettings::class);

        assert($service instanceof FetchSettings);

        return $service->fetch($fetchModel);
    }

    public function fetchSetting(
        ?FetchModel $fetchModel = null
    ): ?SettingModel {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchSettings($fetchModel)[0] ?? null;
    }
}
