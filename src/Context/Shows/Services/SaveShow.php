<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Events\SaveShowAfterSave;
use App\Context\Shows\Events\SaveShowBeforeSave;
use App\Context\Shows\Events\SaveShowSaveFailed;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Services\Internal\SaveShowExisting;
use App\Context\Shows\Services\Internal\SaveShowNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SaveShow
{
    private DatabaseTransactionManager $transactionManager;
    private SaveShowNew $saveNew;
    private SaveShowExisting $saveExisting;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private EventDispatcherInterface $eventDispatcher;
    private ValidateUniqueShowSlug $validateUniqueShowSlug;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SaveShowNew $saveNew,
        SaveShowExisting $saveExisting,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        EventDispatcherInterface $eventDispatcher,
        ValidateUniqueShowSlug $validateUniqueShowSlug
    ) {
        $this->transactionManager     = $transactionManager;
        $this->saveNew                = $saveNew;
        $this->saveExisting           = $saveExisting;
        $this->uuidFactory            = $uuidFactory;
        $this->eventDispatcher        = $eventDispatcher;
        $this->validateUniqueShowSlug = $validateUniqueShowSlug;
    }

    public function save(ShowModel $show): Payload
    {
        try {
            return $this->innerRun($show);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            try {
                $failedEvent = new SaveShowSaveFailed($show);

                $this->eventDispatcher->dispatch($failedEvent);
            } catch (Throwable $e) {
            }

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(ShowModel $show): Payload
    {
        if (
            ! $this->validateUniqueShowSlug->validate(
                $show->slug,
                $show->id,
            )
        ) {
            throw new Exception();
        }

        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($show->id === '') {
            $show->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $beforeEvent = new SaveShowBeforeSave($show);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        if ($isNew) {
            $payload = $this->saveNew->save($show);
        } else {
            $payload = $this->saveExisting->save($show);
        }

        $afterEvent = new SaveShowAfterSave($show);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
