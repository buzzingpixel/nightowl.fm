<?php

declare(strict_types=1);

namespace App\Persistence\Users;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class UserPasswordResetTokenRecord extends Record
{
    protected static string $tableName = 'user_password_reset_tokens';

    public string $user_id = '';

    public string $created_at = '';
}
