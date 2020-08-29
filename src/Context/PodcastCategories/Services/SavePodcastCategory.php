<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Services;

use App\Context\PodcastCategories\Events\SavePodcastCategoryAfterSave;
use App\Context\PodcastCategories\Events\SavePodcastCategoryBeforeSave;
use App\Context\PodcastCategories\Events\SavePodcastCategorySaveFailed;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\Services\Internal\SavePodcastCategoryExisting;
use App\Context\PodcastCategories\Services\Internal\SavePodcastCategoryNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SavePodcastCategory
{
    private DatabaseTransactionManager $transactionManager;
    private SavePodcastCategoryNew $saveNew;
    private SavePodcastCategoryExisting $saveExisting;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SavePodcastCategoryNew $saveNew,
        SavePodcastCategoryExisting $saveExisting,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->transactionManager = $transactionManager;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
        $this->uuidFactory        = $uuidFactory;
        $this->eventDispatcher    = $eventDispatcher;
    }

    public function save(PodcastCategoryModel $model): Payload
    {
        try {
            return $this->innerRun($model);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            try {
                $this->eventDispatcher->dispatch(
                    new SavePodcastCategorySaveFailed(
                        $model
                    ),
                );
            } catch (Throwable $e) {
            }

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(PodcastCategoryModel $model): Payload
    {
        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($model->id === '') {
            $model->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $beforeEvent = new SavePodcastCategoryBeforeSave(
            $model
        );

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        if ($isNew) {
            $payload = $this->saveNew->save($model);
        } else {
            $payload = $this->saveExisting->save($model);
        }

        $afterEvent = new SavePodcastCategoryAfterSave($model);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
