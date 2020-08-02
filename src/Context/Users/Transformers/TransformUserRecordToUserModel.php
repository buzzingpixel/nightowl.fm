<?php

declare(strict_types=1);

namespace App\Context\Users\Transformers;

use App\Context\Users\Models\UserModel;
use App\Persistence\Constants;
use App\Persistence\Users\UserRecord;
use DateTimeZone;
use Safe\DateTimeImmutable;

use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformUserRecordToUserModel
{
    public function __invoke(UserRecord $record): UserModel
    {
        $model = new UserModel();

        $model->id = $record->id;

        $model->isAdmin = in_array(
            $record->is_admin,
            ['1', 1, true],
            true,
        );

        $model->emailAddress = $record->email_address;

        $model->passwordHash = $record->password_hash;

        $model->isActive = in_array(
            $record->is_active,
            ['1', 1, true],
            true,
        );

        $model->timezone = new DateTimeZone(
            $record->timezone
        );

        $createdAt = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record->created_at
        );

        $model->createdAt = $createdAt;

        return $model;
    }
}
