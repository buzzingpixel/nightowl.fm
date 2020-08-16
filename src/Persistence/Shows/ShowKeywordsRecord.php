<?php

declare(strict_types=1);

namespace App\Persistence\Shows;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class ShowKeywordsRecord extends Record
{
    protected static string $tableName = 'show_keywords';

    public string $show_id = '';

    public string $keyword_id = '';
}
