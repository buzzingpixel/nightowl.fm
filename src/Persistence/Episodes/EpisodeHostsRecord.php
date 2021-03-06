<?php

declare(strict_types=1);

namespace App\Persistence\Episodes;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class EpisodeHostsRecord extends Record
{
    protected static string $tableName = 'episode_hosts';

    public string $episode_id = '';

    public string $person_id = '';
}
