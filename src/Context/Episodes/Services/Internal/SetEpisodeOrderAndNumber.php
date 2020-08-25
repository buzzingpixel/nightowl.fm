<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Episodes\EpisodeRecord;
use App\Persistence\RecordQueryFactory;

use function assert;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SetEpisodeOrderAndNumber
{
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(
        RecordQueryFactory $recordQueryFactory
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
    }

    public function set(EpisodeModel $episode): void
    {
        if (! $episode->isPublished) {
            return;
        }

        $this->setEpisodeNumber($episode);

        $this->setOrder($episode);
    }

    private function setEpisodeNumber(EpisodeModel $episode): void
    {
        if ($episode->episodeType !== EpisodeConstants::EPISODE_TYPE_NUMBERED) {
            return;
        }

        if ($episode->number > 0) {
            return;
        }

        $record = $this->recordQueryFactory
            ->make(new EpisodeRecord())
            ->withWhere(
                'episode_type',
                EpisodeConstants::EPISODE_TYPE_NUMBERED
            )
            ->withWhere(
                'status',
                EpisodeConstants::EPISODE_STATUS_LIVE
            )
            ->withWhere('show_id', $episode->show->id)
            ->withWhere('id', $episode->id, '!=')
            ->withOrder('display_order', 'desc')
            ->one();

        assert($record instanceof EpisodeRecord || $record === null);

        if ($record === null) {
            $episode->number = 1;

            return;
        }

        $previousNumber = (int) $record->number;

        $episode->number = $previousNumber + 1;
    }

    private function setOrder(EpisodeModel $episode): void
    {
        if ($episode->displayOrder > 0) {
            return;
        }

        $record = $this->recordQueryFactory
            ->make(new EpisodeRecord())
            ->withWhere(
                'status',
                EpisodeConstants::EPISODE_STATUS_LIVE
            )
            ->withWhere('show_id', $episode->show->id)
            ->withWhere('id', $episode->id, '!=')
            ->withOrder('display_order', 'desc')
            ->one();

        assert($record instanceof EpisodeRecord || $record === null);

        if ($record === null) {
            $episode->displayOrder = 1;

            return;
        }

        $previousOrder = (int) $record->display_order;

        $episode->displayOrder = $previousOrder + 1;
    }
}
