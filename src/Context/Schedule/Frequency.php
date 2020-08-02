<?php

declare(strict_types=1);

namespace App\Context\Schedule;

class Frequency
{
    public const ALWAYS                = 'Always';
    public const FIVE_MINUTES          = 'FiveMinutes';
    public const TEN_MINUTES           = 'TenMinutes';
    public const THIRTY_MINUTES        = 'ThirtyMinutes';
    public const HOUR                  = 'Hour';
    public const DAY                   = 'Day';
    public const WEEK                  = 'Week';
    public const MONTH                 = 'Month';
    public const DAY_AT_MIDNIGHT       = 'DayAtMidnight';
    public const SATURDAY_AT_MIDNIGHT  = 'SaturdayAtMidnight';
    public const SUNDAY_AT_MIDNIGHT    = 'SundayAtMidnight';
    public const MONDAY_AT_MIDNIGHT    = 'MondayAtMidnight';
    public const TUESDAY_AT_MIDNIGHT   = 'TuesdayAtMidnight';
    public const WEDNESDAY_AT_MIDNIGHT = 'WednesdayAtMidnight';
    public const THURSDAY_AT_MIDNIGHT  = 'ThursdayAtMidNight';
    public const FRIDAY_AT_MIDNIGHT    = 'FridayAtMidnight';
}
