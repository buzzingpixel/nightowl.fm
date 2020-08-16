<?php

declare(strict_types=1);

namespace App\Context\Shows;

interface ShowConstants
{
    public const SHOW_STATUS_LIVE              = 'live';
    public const SHOW_STATUS_LIVE_LABEL        = 'Live';
    public const SHOW_STATUS_COMING_SOON       = 'coming_soon';
    public const SHOW_STATUS_COMING_SOON_LABEL = 'Coming Soon';
    public const SHOW_STATUS_HIDDEN            = 'hidden';
    public const SHOW_STATUS_HIDDEN_LABEL      = 'Hidden';
    public const SHOW_STATUS_RETIRED           = 'retired';
    public const SHOW_STATUS_RETIRED_LABEL     = 'Retired';

    public const STATUSES = [
        self::SHOW_STATUS_LIVE,
        self::SHOW_STATUS_COMING_SOON,
        self::SHOW_STATUS_HIDDEN,
        self::SHOW_STATUS_RETIRED,
    ];

    public const STATUSES_SELECT_ARRAY = [
        [
            'value' => self::SHOW_STATUS_LIVE,
            'label' => self::SHOW_STATUS_LIVE_LABEL,
        ],
        [
            'value' => self::SHOW_STATUS_COMING_SOON,
            'label' => self::SHOW_STATUS_COMING_SOON_LABEL,
        ],
        [
            'value' => self::SHOW_STATUS_HIDDEN,
            'label' => self::SHOW_STATUS_HIDDEN_LABEL,
        ],
        [
            'value' => self::SHOW_STATUS_RETIRED,
            'label' => self::SHOW_STATUS_RETIRED_LABEL,
        ],
    ];
}
