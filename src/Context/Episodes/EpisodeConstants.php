<?php

declare(strict_types=1);

namespace App\Context\Episodes;

interface EpisodeConstants
{
    public const EPISODE_STATUS_LIVE        = 'live';
    public const EPISODE_STATUS_LIVE_LABEL  = 'Live';
    public const EPISODE_STATUS_DRAFT       = 'draft';
    public const EPISODE_STATUS_DRAFT_LABEL = 'Draft';

    public const STATUSES = [
        self::EPISODE_STATUS_LIVE,
        self::EPISODE_STATUS_DRAFT,
    ];

    public const STATUSES_SELECT_ARRAY = [
        [
            'value' => self::EPISODE_STATUS_LIVE,
            'label' => self::EPISODE_STATUS_LIVE_LABEL,
        ],
        [
            'value' => self::EPISODE_STATUS_DRAFT,
            'label' => self::EPISODE_STATUS_DRAFT_LABEL,
        ],
    ];

    public const EPISODE_TYPE_NUMBERED       = 'numbered';
    public const EPISODE_TYPE_NUMBERED_LABEL = 'Numbered';
    public const EPISODE_TYPE_INSERT         = 'insert';
    public const EPISODE_TYPE_INSERT_LABEL   = 'Insert';

    public const TYPES = [
        self::EPISODE_TYPE_NUMBERED,
        self::EPISODE_TYPE_INSERT,
    ];

    public const TYPES_SELECT_ARRAY = [
        [
            'value' => self::EPISODE_TYPE_NUMBERED,
            'label' => self::EPISODE_TYPE_NUMBERED_LABEL,
        ],
        [
            'value' => self::EPISODE_TYPE_INSERT,
            'label' => self::EPISODE_TYPE_INSERT_LABEL,
        ],
    ];
}
