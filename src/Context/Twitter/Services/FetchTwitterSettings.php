<?php

declare(strict_types=1);

namespace App\Context\Twitter\Services;

use App\Context\Settings\Models\FetchModel;
use App\Context\Settings\SettingsApi;
use App\Context\Twitter\Models\TwitterSettingsModel;
use App\Context\Twitter\Transformers\SettingToModel;

class FetchTwitterSettings
{
    private SettingsApi $settingsApi;
    private SettingToModel $settingToModel;

    public function __construct(
        SettingsApi $settingsApi,
        SettingToModel $settingToModel
    ) {
        $this->settingsApi    = $settingsApi;
        $this->settingToModel = $settingToModel;
    }

    public function fetch(): TwitterSettingsModel
    {
        $fetchModel = new FetchModel();

        $fetchModel->keys = [TwitterSettingsModel::$settingKey];

        $setting = $this->settingsApi->fetchSetting($fetchModel);

        if ($setting === null) {
            return new TwitterSettingsModel();
        }

        return $this->settingToModel->transform($setting);
    }
}
