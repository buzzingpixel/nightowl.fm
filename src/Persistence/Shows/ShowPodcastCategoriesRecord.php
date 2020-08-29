<?php

declare(strict_types=1);

namespace App\Persistence\Shows;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class ShowPodcastCategoriesRecord extends Record
{
    protected static string $tableName = 'show_podcast_categories';

    public string $show_id = '';

    public string $podcast_category_id = '';
}
