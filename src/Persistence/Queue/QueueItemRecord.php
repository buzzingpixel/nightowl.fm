<?php

declare(strict_types=1);

namespace App\Persistence\Queue;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class QueueItemRecord extends Record
{
    protected static string $tableName = 'queue_items';

    public string $queue_id;

    /** @var float|int|string */
    public $run_order = 1;

    /** @var int|bool|string */
    public $is_finished = '0';

    public ?string $finished_at = null;

    public string $class = '';

    public string $method = '';

    public ?string $context = null;
}
