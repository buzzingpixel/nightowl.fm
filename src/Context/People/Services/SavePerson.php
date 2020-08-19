<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Models\PersonModel;
use App\Context\People\Services\Internal\SavePersonExisting;
use App\Context\People\Services\Internal\SavePersonNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Throwable;

class SavePerson
{
    private DatabaseTransactionManager $transactionManager;
    private SavePersonNew $saveNew;
    private SavePersonExisting $saveExisting;
    private SaveNewProfilePhoto $saveNewProfilePhoto;
    private DeleteUserProfilePhoto $deleteUserProfilePhoto;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private ValidateUniquePersonSlug $validateUniquePersonSlug;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        SavePersonNew $saveNew,
        SavePersonExisting $saveExisting,
        SaveNewProfilePhoto $saveNewProfilePhoto,
        DeleteUserProfilePhoto $deleteUserProfilePhoto,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        ValidateUniquePersonSlug $validateUniquePersonSlug
    ) {
        $this->transactionManager       = $transactionManager;
        $this->saveNew                  = $saveNew;
        $this->saveExisting             = $saveExisting;
        $this->saveNewProfilePhoto      = $saveNewProfilePhoto;
        $this->deleteUserProfilePhoto   = $deleteUserProfilePhoto;
        $this->uuidFactory              = $uuidFactory;
        $this->validateUniquePersonSlug = $validateUniquePersonSlug;
    }

    public function save(PersonModel $person): Payload
    {
        try {
            if (
                ! $this->validateUniquePersonSlug->validate(
                    $person->slug,
                    $person->id,
                )
            ) {
                throw new Exception();
            }

            $this->transactionManager->beginTransaction();

            $isNew = false;

            if ($person->id === '') {
                $person->id = $this->uuidFactory->uuid1()->toString();

                $isNew = true;
            }

            if ($person->newPhotoFileLocation !== '') {
                $this->saveNewProfilePhoto->save($person);
            }

            if ($isNew) {
                $payload = $this->saveNew->save($person);
            } else {
                $payload = $this->saveExisting->save($person);
            }

            $this->transactionManager->commit();

            return $payload;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            $this->deleteUserProfilePhoto->delete($person);

            return new Payload(Payload::STATUS_ERROR);
        }
    }
}
