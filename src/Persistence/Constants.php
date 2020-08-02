<?php

declare(strict_types=1);

namespace App\Persistence;

class Constants
{
    /**
     * Strangely, while Postgres can handle PHP's ISO 8601 representation
     * (DateTime::ATOM) on input, it spits out a modified version
     * (without the T) on output. The format below can be used with
     * DateTime::createFromFormat() to convert to proper ISO 8601
     *
     * @see https://www.postgresql.org/docs/current/datatype-datetime.html#DATATYPE-DATETIME-OUTPUT
     */
    public const POSTGRES_OUTPUT_FORMAT = 'Y-m-d H:i:sP';
}
