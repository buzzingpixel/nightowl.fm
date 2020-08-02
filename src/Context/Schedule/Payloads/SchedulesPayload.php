<?php

declare(strict_types=1);

namespace App\Context\Schedule\Payloads;

use App\Context\Schedule\Models\ScheduleItemModel;
use App\Payload\SpecificPayload;

class SchedulesPayload extends SpecificPayload
{
    /** @var ScheduleItemModel[] */
    private array $schedules = [];

    /**
     * @param ScheduleItemModel[] $schedules
     */
    protected function setSchedules(array $schedules): void
    {
        $this->schedules = $schedules;
    }

    /**
     * @return ScheduleItemModel[]
     */
    public function getSchedules(): array
    {
        return $this->schedules;
    }
}
