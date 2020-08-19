<?php

declare(strict_types=1);

namespace App\Persistence\Series;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

use App\Persistence\Record;

class SeriesRecord extends Record
{
    protected static string $tableName = 'series';

    public string $show_id;

    public string $title;

    public string $slug;

    public string $description;
}
