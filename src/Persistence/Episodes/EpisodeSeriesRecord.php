<?php

declare(strict_types=1);

namespace App\Persistence\Episodes;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class EpisodeSeriesRecord extends Record
{
    protected static string $tableName = 'episode_series';

    public string $episode_id = '';

    public string $series_id = '';
}
