<?php

declare(strict_types=1);

namespace App\Context\Schedule\Services;

use function is_numeric;
use function mb_strtolower;

class TranslateRunEvery
{
    public const RUN_EVERY_MAP = [
        'always' => 0,
        'fiveminutes' => 5,
        'tenminutes' => 10,
        'thirtyminutes' => 30,
        'hour' => 60,
        'day' => 1440,
        'week' => 10080,
        'month' => 43800,
        'dayatmidnight' => 'dayatmidnight',
        'saturdayatmidnight' => 'saturdayatmidnight',
        'sundayatmidnight' => 'sundayatmidnight',
        'mondayatmidnight' => 'mondayatmidnight',
        'tuesdayatmidnight' => 'tuesdayatmidnight',
        'wednesdayatmidnight' => 'wednesdayatmidnight',
        'thursdayatmidnight' => 'thursdayatmidnight',
        'fridayatmidnight' => 'fridayatmidnight',
    ];

    /**
     * Translates run every into actionable values.
     * - If the value of runEvery is numeric, it is assumed to be minutes and
     *   will be converted to seconds
     * - Else if the runEvery value is not set on the RUN_EVERY_MAP, a 0 will be
     *   returned (same value as always)
     * - Else if the runEvery mapped value is numeric, it is minutes and will be
     *   converted to seconds and returned
     * - Else the mapped value will be returned
     *
     * @param float|int|string $val
     *
     * @return float|int|string
     */
    public function getTranslatedValue($val)
    {
        if (is_numeric($val)) {
            return ((int) $val) * 60;
        }

        $val = (string) mb_strtolower($val);

        if (! isset(self::RUN_EVERY_MAP[$val])) {
            return 0;
        }

        $mappedVal = self::RUN_EVERY_MAP[$val];

        if (is_numeric($mappedVal)) {
            $mappedVal = (int) $mappedVal;

            return $mappedVal * 60;
        }

        return $mappedVal;
    }
}
