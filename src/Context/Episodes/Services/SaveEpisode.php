<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\Events\SaveEpisodeAfterSave;
use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Events\SaveEpisodeSaveFailed;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Services\Internal\SaveEpisodeExisting;
use App\Context\Episodes\Services\Internal\SaveEpisodeNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SaveEpisode
{
    private DatabaseTransactionManager $transactionManager;
    private SaveEpisodeNew $saveNew;
    private SaveEpisodeExisting $saveExisting;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SaveEpisodeNew $saveNew,
        SaveEpisodeExisting $saveExisting,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->transactionManager = $transactionManager;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
        $this->uuidFactory        = $uuidFactory;
        $this->eventDispatcher    = $eventDispatcher;
    }

    public function save(EpisodeModel $episode): Payload
    {
        try {
            return $this->innerRun($episode);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            try {
                $this->eventDispatcher->dispatch(
                    new SaveEpisodeSaveFailed($episode)
                );
            } catch (Throwable $e) {
            }

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(EpisodeModel $episode): Payload
    {
        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($episode->id === '') {
            $episode->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $beforeEvent = new SaveEpisodeBeforeSave($episode);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        if ($isNew) {
            $payload = $this->saveNew->save($episode);

            if ($payload->getStatus() !== Payload::STATUS_CREATED) {
                $this->transactionManager->rollBack();

                return $payload;
            }
        } else {
            $payload = $this->saveExisting->save($episode);

            if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
                $this->transactionManager->rollBack();

                return $payload;
            }
        }

        $afterEvent = new SaveEpisodeAfterSave($episode);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
