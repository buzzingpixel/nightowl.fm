<?php

declare(strict_types=1);

namespace App\Persistence\Shows;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class ShowRecord extends Record
{
    protected static string $tableName = 'shows';

    public string $title = '';

    public string $slug = '';

    public string $artwork_file_location = '';

    public string $status = '';

    public string $description = '';

    /** @var int|bool|string */
    public $explicit = '0';

    public string $itunes_link = '';

    public string $google_play_link = '';

    public string $stitcher_link = '';

    public string $spotify_link = '';

    public string $patreon_link = '';

    public string $patreon_cta = '';

    public string $patreon_headline = '';

    public string $patreon_description = '';
}
