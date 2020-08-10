<?php

declare(strict_types=1);

namespace App\Persistence\People;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class PersonRecord extends Record
{
    protected static string $tableName = 'people';

    public string $first_name = '';

    public string $last_name = '';

    public string $slug = '';

    public string $email = '';

    public string $photo_file_location = '';

    public string $photo_preference = '';

    public string $bio = '';

    public string $location = '';

    public string $facebook_page_slug = '';

    public string $twitter_handle = '';

    public string $links = '';
}
