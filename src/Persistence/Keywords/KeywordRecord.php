<?php

declare(strict_types=1);

namespace App\Persistence\Keywords;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class KeywordRecord extends Record
{
    protected static string $tableName = 'keywords';

    public string $keyword = '';

    public string $slug = '';
}
