<?php

declare(strict_types=1);

namespace App\Persistence\Shows;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class ShowHostsRecord extends Record
{
    protected static string $tableName = 'show_hosts';

    public string $show_id = '';

    public string $person_id = '';
}
