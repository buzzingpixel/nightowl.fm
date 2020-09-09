<?php

declare(strict_types=1);

namespace App\Context\Twitter\Services;

use App\Context\Settings\Services\SaveSetting;
use App\Context\Twitter\Models\TwitterSettingsModel;
use App\Context\Twitter\Transformers\ModelToSetting;
use App\Payload\Payload;

class SaveTwitterSettings
{
    private ModelToSetting $modelToSetting;
    private SaveSetting $saveSetting;

    public function __construct(
        ModelToSetting $modelToSetting,
        SaveSetting $saveSetting
    ) {
        $this->modelToSetting = $modelToSetting;
        $this->saveSetting    = $saveSetting;
    }

    public function save(TwitterSettingsModel $model): Payload
    {
        $payload = $this->saveSetting->save(
            $this->modelToSetting->transform($model)
        );

        $settingId = (string) ($payload->getResult()['id'] ?? '');

        if ($settingId !== '') {
            $model->settingId = $settingId;
        }

        return $payload;
    }
}
