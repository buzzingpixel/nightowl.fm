<?php

declare(strict_types=1);

namespace App\Persistence\PodcastCategories;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

use App\Persistence\Record;

class PodcastCategoryRecord extends Record
{
    protected static string $tableName = 'podcast_categories';

    public ?string $parent_id = null;

    public string $parent_chain = '';

    public string $name = '';
}
