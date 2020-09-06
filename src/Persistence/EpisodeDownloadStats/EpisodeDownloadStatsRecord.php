<?php

declare(strict_types=1);

namespace App\Persistence\EpisodeDownloadStats;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

use App\Persistence\Record;

class EpisodeDownloadStatsRecord extends Record
{
    protected static string $tableName = 'episode_downloads_stats';

    public string $episode_id = '';

    /** @var int|bool|string */
    public $total_downloads = '0';

    /** @var int|bool|string */
    public $downloads_past_thirty_days = '0';

    /** @var int|bool|string */
    public $downloads_past_sixty_days = '0';

    /** @var int|bool|string */
    public $downloads_past_year = '0';
}
