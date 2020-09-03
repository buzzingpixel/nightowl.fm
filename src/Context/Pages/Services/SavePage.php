<?php

declare(strict_types=1);

namespace App\Context\Pages\Services;

use App\Context\Pages\Events\SavePageAfterSave;
use App\Context\Pages\Events\SavePageBeforeSave;
use App\Context\Pages\Events\SavePageFailed;
use App\Context\Pages\Models\PageModel;
use App\Context\Pages\Services\Internal\SavePageExisting;
use App\Context\Pages\Services\Internal\SavePageNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SavePage
{
    private DatabaseTransactionManager $transactionManager;
    private SavePageNew $saveNew;
    private SavePageExisting $saveExisting;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private ValidateUniquePageUri $validateUniquePageUri;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SavePageNew $saveNew,
        SavePageExisting $saveExisting,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        ValidateUniquePageUri $validateUniquePageUri,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->transactionManager    = $transactionManager;
        $this->saveNew               = $saveNew;
        $this->saveExisting          = $saveExisting;
        $this->uuidFactory           = $uuidFactory;
        $this->validateUniquePageUri = $validateUniquePageUri;
        $this->eventDispatcher       = $eventDispatcher;
    }

    public function save(PageModel $page): Payload
    {
        try {
            return $this->innerRun($page);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            try {
                $this->eventDispatcher->dispatch(
                    new SavePageFailed($page)
                );
            } catch (Throwable $e) {
            }

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(PageModel $page): Payload
    {
        $this->transactionManager->beginTransaction();

        if (
            ! $this->validateUniquePageUri->validate(
                $page->uri,
                $page->id,
            )
        ) {
            throw new Exception();
        }

        $isNew = false;

        if ($page->id === '') {
            $page->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        $beforeEvent = new SavePageBeforeSave($page);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        if ($isNew) {
            $payload = $this->saveNew->save($page);
        } else {
            $payload = $this->saveExisting->save($page);
        }

        $afterEvent = new SavePageAfterSave($page);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
