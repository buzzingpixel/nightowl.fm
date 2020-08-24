<?php

declare(strict_types=1);

namespace App\Persistence\Episodes;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class EpisodeKeywordsRecord extends Record
{
    protected static string $tableName = 'episode_keywords';

    public string $episode_id = '';

    public string $keyword_id = '';
}
