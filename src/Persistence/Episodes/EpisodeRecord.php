<?php

declare(strict_types=1);

namespace App\Persistence\Episodes;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class EpisodeRecord extends Record
{
    protected static string $tableName = 'episodes';

    public string $show_id = '';

    public string $title = '';

    public string $status = '';

    public string $description = '';

    public string $file_location = '';

    /** @var float|int|string */
    public $file_runtime_seconds = 0.0;

    public string $file_size_bytes = '';

    public string $file_mime_type = '';

    public string $file_format = '';

    public string $episode_type = '';

    /** @var int|bool|string */
    public $explicit = '0';

    public string $show_notes = '';

    public ?string $publish_at = null;

    public ?string $published_at = null;

    /** @var int|bool|string */
    public $is_published = '0';

    /** @var float|int|string */
    public $number = 0;

    /** @var float|int|string */
    public $display_order = 0;

    public string $created_at;

    public string $old_guid = '';
}
