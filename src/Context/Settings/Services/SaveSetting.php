<?php

declare(strict_types=1);

namespace App\Context\Settings\Services;

use App\Context\Settings\Models\SettingModel;
use App\Context\Settings\Services\Internal\SaveSettingExisting;
use App\Context\Settings\Services\Internal\SaveSettingNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Throwable;

class SaveSetting
{
    private DatabaseTransactionManager $transactionManager;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private SaveSettingNew $saveNew;
    private SaveSettingExisting $saveExisting;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        SaveSettingNew $saveNew,
        SaveSettingExisting $saveExisting
    ) {
        $this->transactionManager = $transactionManager;
        $this->uuidFactory        = $uuidFactory;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
    }

    public function save(SettingModel $model): Payload
    {
        try {
            return $this->innerSave($model);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Throwable
     */
    private function innerSave(SettingModel $model): Payload
    {
        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($model->id === '') {
            $model->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        if ($isNew) {
            $payload = $this->saveNew->save($model);
        } else {
            $payload = $this->saveExisting->save($model);
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
