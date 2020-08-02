<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use App\Persistence\Users\UserPasswordResetTokenRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use DateTimeInterface;
use DateTimeZone;
use Safe\DateTimeImmutable;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class GeneratePasswordResetToken
{
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private SaveNewRecord $saveNewRecord;

    public function __construct(
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        SaveNewRecord $saveNewRecord
    ) {
        $this->uuidFactory   = $uuidFactory;
        $this->saveNewRecord = $saveNewRecord;
    }

    public function __invoke(UserModel $user): Payload
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $record = new UserPasswordResetTokenRecord();

        /** @noinspection PhpUnhandledExceptionInspection */
        $record->id = $this->uuidFactory->uuid1()->toString();

        $record->user_id = $user->id;

        $record->created_at = $currentDate->format(
            DateTimeInterface::ATOM
        );

        return ($this->saveNewRecord)($record);
    }
}
