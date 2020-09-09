<?php

declare(strict_types=1);

namespace App\Context\Twitter\Transformers;

use App\Context\Settings\Models\SettingModel;
use App\Context\Twitter\Models\TwitterSettingsModel;

class SettingToModel
{
    public function transform(SettingModel $setting): TwitterSettingsModel
    {
        $twitterSettings = TwitterSettingsModel::fromArray(
            $setting->value
        );

        $twitterSettings->settingId = $setting->id;

        return $twitterSettings;
    }
}
