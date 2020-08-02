<?php

declare(strict_types=1);

namespace App\Persistence\Users;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class UserRecord extends Record
{
    protected static string $tableName = 'users';

    /** @var string int|bool|string */
    public string $is_admin = '0';

    public string $email_address = '';

    public string $password_hash = '';

    /** @var int|bool|string */
    public $is_active = '1';

    public string $timezone = '';

    public string $created_at = '';
}
