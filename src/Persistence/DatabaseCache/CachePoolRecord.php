<?php

declare(strict_types=1);

namespace App\Persistence\DatabaseCache;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class CachePoolRecord extends Record
{
    protected static string $tableName = 'cache_pool';

    public string $key = '';

    public string $value = '';

    public ?string $expires_at = null;
}
