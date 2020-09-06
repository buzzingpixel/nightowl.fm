<?php

declare(strict_types=1);

namespace App\Persistence\EpisodeDownloadStats;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class EpisodeDownloadTrackerRecord extends Record
{
    protected static string $tableName = 'episode_download_tracker';

    public string $episode_id = '';

    /** @var int|bool|string */
    public $is_full_range = '0';

    /** @var float|int|string */
    public $range_start = 0;

    /** @var float|int|string */
    public $range_end = 0;

    public string $downloaded_at;
}
