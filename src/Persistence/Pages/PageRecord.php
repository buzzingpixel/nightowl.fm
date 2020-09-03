<?php

declare(strict_types=1);

namespace App\Persistence\Pages;

use App\Persistence\Record;

class PageRecord extends Record
{
    protected static string $tableName = 'pages';

    public string $title = '';

    public string $uri = '';

    public string $content = '';
}
