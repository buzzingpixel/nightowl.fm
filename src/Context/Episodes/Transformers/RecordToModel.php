<?php

declare(strict_types=1);

namespace App\Context\Episodes\Transformers;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\Constants;
use App\Persistence\Episodes\EpisodeRecord;
use Safe\DateTimeImmutable;

use function array_walk;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class RecordToModel
{
    /**
     * @param PersonModel[]  $hosts
     * @param PersonModel[]  $guests
     * @param KeywordModel[] $keywords
     * @param SeriesModel[]  $series
     */
    public function transform(
        EpisodeRecord $record,
        ShowModel $showModel,
        array $hosts = [],
        array $guests = [],
        array $keywords = [],
        array $series = []
    ): EpisodeModel {
        $model = new EpisodeModel();

        $model->id = $record->id;

        $model->show = $showModel;

        $model->title = $record->title;

        $model->status = $record->status;

        $model->description = $record->description;

        $model->fileLocation = $record->file_location;

        $model->fileRuntimeSeconds = (float) $record->file_runtime_seconds;

        $model->fileSizeBytes = $record->file_size_bytes;

        $model->fileMimeType = $record->file_mime_type;

        $model->fileFormat = $record->file_format;

        $model->episodeType = $record->episode_type;

        $model->explicit = in_array(
            $record->explicit,
            [
                true,
                'true',
                '1',
                1,
            ],
            true,
        );

        $model->showNotes = $record->show_notes;

        if ($record->publish_at !== null && $record->publish_at !== '') {
            $publishAt = DateTimeImmutable::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                $record->publish_at,
            );

            $model->publishAt = $publishAt;
        }

        if ($record->published_at !== null && $record->published_at !== '') {
            $publishedAt = DateTimeImmutable::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                $record->published_at,
            );

            $model->publishedAt = $publishedAt;
        }

        $model->isPublished = in_array(
            $record->is_published,
            [
                true,
                'true',
                '1',
                1,
            ],
            true,
        );

        $model->number = (int) $record->number;

        $model->displayOrder = (int) $record->display_order;

        $model->oldGuid = $record->old_guid;

        $createdAt = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record->created_at
        );

        $model->createdAt = $createdAt;

        array_walk($hosts, [$model, 'addHost']);

        array_walk($guests, [$model, 'addGuest']);

        array_walk($keywords, [$model, 'addKeyword']);

        array_walk($series, [$model, 'addSeries']);

        return $model;
    }
}
