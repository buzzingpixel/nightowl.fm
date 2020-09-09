<?php

declare(strict_types=1);

namespace App\Persistence\Analytics;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

use App\Persistence\Record;

class AnalyticsRecord extends Record
{
    protected static string $tableName = 'analytics';

    public string $cookie_id = '';

    public ?string $user_id = null;

    /** @var int|bool|string */
    public $logged_in_on_page_load = '1';

    public string $uri = '';

    public string $date = '';
}
