<?php

declare(strict_types=1);

namespace App\Persistence\Users;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class UserSessionRecord extends Record
{
    protected static string $tableName = 'user_sessions';

    public string $user_id = '';

    public string $created_at = '';

    public string $last_touched_at = '';
}
