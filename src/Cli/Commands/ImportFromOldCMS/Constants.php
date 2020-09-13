<?php

declare(strict_types=1);

namespace App\Cli\Commands\ImportFromOldCMS;

interface Constants
{
    // public const BASE_IMPORT_URL = 'https://nightowl-craft.localtest.me:29765';
    // public const BASE_IMPORT_URL = 'http://migration.nightowl.fm';
    public const BASE_IMPORT_URL = 'https://nightowl.fm';

    public const GET_USERS = 'index.php?p=actions/nightcast/migration/getUsers';

    public const GET_SHOWS = 'index.php?p=actions/nightcast/migration/getShows';

    public const GET_SERIES = 'index.php?p=actions/nightcast/migration/getSeries';

    public const GET_EPISODES = 'index.php?p=actions/nightcast/migration/getEpisodes';
}
