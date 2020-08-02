<?php

declare(strict_types=1);

namespace App\Persistence\Queue;

use App\Persistence\Record;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

class QueueRecord extends Record
{
    protected static string $tableName = 'queue';

    public string $handle;

    public string $display_name;

    /** @var int|bool|string */
    public $has_started = '0';

    /** @var int|bool|string */
    public $is_running = '0';

    public string $assume_dead_after = '';

    public string $initial_assume_dead_after = '';

    /** @var int|bool|string */
    public $is_finished = '0';

    /** @var int|bool|string */
    public $finished_due_to_error = '0';

    public string $error_message;

    /** @var float|int|string */
    public $percent_complete = 0;

    public string $added_at = '';

    public ?string $finished_at = null;
}
