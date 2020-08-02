<?php

declare(strict_types=1);

namespace App\Context\Users\Transformers;

use App\Context\Users\Models\UserModel;
use App\Persistence\Users\UserRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformUserModelToUserRecord
{
    public function __invoke(UserModel $model): UserRecord
    {
        $record = new UserRecord();

        $record->id = $model->id;

        $record->is_admin = $model->isAdmin ? '1' : '0';

        $record->email_address = $model->emailAddress;

        $record->password_hash = $model->passwordHash;

        $record->is_active = $model->isActive ? '1' : '0';

        $record->timezone = $model->timezone->getName();

        $record->created_at = $model->createdAt->format(
            DateTimeInterface::ATOM
        );

        return $record;
    }
}
