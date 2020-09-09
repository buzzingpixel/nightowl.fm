<?php

declare(strict_types=1);

namespace App\Persistence\Settings;

use App\Persistence\Record;

class SettingRecord extends Record
{
    protected static string $tableName = 'settings';

    public string $key = '';

    public string $value = '';
}
