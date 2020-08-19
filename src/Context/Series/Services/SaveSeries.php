<?php

declare(strict_types=1);

namespace App\Context\Series\Services;

use App\Context\Series\Events\SaveSeriesAfterSave;
use App\Context\Series\Events\SaveSeriesBeforeSave;
use App\Context\Series\Events\SaveSeriesSaveFailed;
use App\Context\Series\Models\SeriesModel;
use App\Context\Series\Services\Internal\SaveExistingSeries;
use App\Context\Series\Services\Internal\SaveNewSeries;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SaveSeries
{
    private DatabaseTransactionManager $transactionManager;
    private SaveNewSeries $saveNew;
    private SaveExistingSeries $saveExisting;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SaveNewSeries $saveNew,
        SaveExistingSeries $saveExisting,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->transactionManager = $transactionManager;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
        $this->uuidFactory        = $uuidFactory;
        $this->eventDispatcher    = $eventDispatcher;
    }

    public function save(SeriesModel $series): Payload
    {
        try {
            return $this->innerRun($series);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            try {
                $this->eventDispatcher->dispatch(
                    new SaveSeriesSaveFailed($series),
                );
            } catch (Throwable $e) {
            }

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(SeriesModel $series): Payload
    {
        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($series->id === '') {
            $series->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $beforeEvent = new SaveSeriesBeforeSave($series);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        $payload = $isNew ?
            $this->saveNew->save($series) :
            $this->saveExisting->save($series);

        $afterEvent = new SaveSeriesAfterSave($series);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
