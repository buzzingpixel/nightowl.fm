<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Events\SaveUserAfterSave;
use App\Context\Users\Events\SaveUserBeforeSave;
use App\Context\Users\Models\UserModel;
use App\Context\Users\Transformers\TransformUserModelToUserRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use App\Utilities\SimpleValidator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Ramsey\Uuid\UuidFactoryInterface;

use function count;
use function mb_strlen;
use function Safe\password_hash;

use const PASSWORD_DEFAULT;

class SaveUser
{
    public const MIN_PASSWORD_LENGTH = 8;

    private FetchUserByEmailAddress $fetchUserByEmailAddress;
    private SaveNewRecord $saveNewRecord;
    private FetchUserById $fetchUserById;
    private SaveExistingRecord $saveExistingRecord;
    private TransformUserModelToUserRecord $transformUserModelToUserRecord;
    private UuidFactoryInterface $uuidFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        FetchUserByEmailAddress $fetchUserByEmailAddress,
        SaveNewRecord $saveNewRecord,
        FetchUserById $fetchUserById,
        SaveExistingRecord $saveExistingRecord,
        TransformUserModelToUserRecord $transformUserModelToUserRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->fetchUserByEmailAddress        = $fetchUserByEmailAddress;
        $this->saveNewRecord                  = $saveNewRecord;
        $this->fetchUserById                  = $fetchUserById;
        $this->saveExistingRecord             = $saveExistingRecord;
        $this->transformUserModelToUserRecord = $transformUserModelToUserRecord;
        $this->uuidFactory                    = $uuidFactory;
        $this->eventDispatcher                = $eventDispatcher;
    }

    public function __invoke(UserModel $userModel): Payload
    {
        $errors = [];

        if ($userModel->passwordHash === '' && $userModel->newPassword === '') {
            $errors['password'] = 'Password is required';
        }

        if (! SimpleValidator::email($userModel->emailAddress)) {
            $errors['emailAddress'] = 'A valid email address is required';
        }

        $newPass = $userModel->newPassword;

        if ($newPass !== '' && mb_strlen($newPass) < self::MIN_PASSWORD_LENGTH) {
            $errors['password'] = 'Password is too short';
        }

        if (count($errors) > 0) {
            return new Payload(Payload::STATUS_NOT_VALID, $errors);
        }

        if ($newPass !== '') {
            /** @phpstan-ignore-next-line */
            $userModel->passwordHash = (string) password_hash(
                $newPass,
                PASSWORD_DEFAULT
            );
        }

        $this->eventDispatcher->dispatch(new SaveUserBeforeSave(
            $userModel
        ));

        if ($userModel->id === '') {
            $payload = $this->saveNewUser($userModel);
        } else {
            $payload = $this->saveExistingUser($userModel);
        }

        $this->eventDispatcher->dispatch(new SaveUserAfterSave(
            $userModel,
            $payload,
        ));

        return $payload;
    }

    private function saveNewUser(UserModel $userModel): Payload
    {
        $email = $userModel->emailAddress;

        $existingUser = ($this->fetchUserByEmailAddress)($email);

        if ($existingUser !== null) {
            return new Payload(Payload::STATUS_NOT_CREATED, [
                'message' => 'User with email address ' . $email . ' already exists',
            ]);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $userModel->id = $this->uuidFactory->uuid1()->toString();

        $userRecord = ($this->transformUserModelToUserRecord)($userModel);

        return ($this->saveNewRecord)($userRecord);
    }

    private function saveExistingUser(UserModel $userModel): Payload
    {
        $existingUser = ($this->fetchUserById)($userModel->id);

        if ($existingUser === null) {
            return new Payload(Payload::STATUS_NOT_FOUND, [
                'message' => 'User with id ' . $userModel->id . ' not found',
            ]);
        }

        $userRecord = ($this->transformUserModelToUserRecord)($userModel);

        return ($this->saveExistingRecord)($userRecord);
    }
}
