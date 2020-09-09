<?php

declare(strict_types=1);

namespace App\Context\Twitter\Transformers;

use App\Context\Settings\Models\SettingModel;
use App\Context\Twitter\Models\TwitterSettingsModel;

class ModelToSetting
{
    public function transform(TwitterSettingsModel $model): SettingModel
    {
        $setting = new SettingModel();

        $setting->id = $model->settingId;

        $setting->key = TwitterSettingsModel::$settingKey;

        $setting->value = $model->asArray();

        return $setting;
    }
}
