<?php

declare(strict_types=1);

namespace App\Context\Episodes\Transformers;

use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Episodes\EpisodeRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class ModelToRecord
{
    public function transform(EpisodeModel $model): EpisodeRecord
    {
        $record = new EpisodeRecord();

        $record->id = $model->id;

        $record->show_id = $model->show->id;

        $record->title = $model->title;

        $record->status = $model->status;

        $record->description = $model->description;

        $record->file_location = $model->fileLocation;

        $record->file_runtime_seconds = (string) $model->fileRuntimeSeconds;

        $record->file_size_bytes = $model->fileSizeBytes;

        $record->file_mime_type = $model->fileMimeType;

        $record->file_format = $model->fileFormat;

        $record->episode_type = $model->episodeType;

        $record->explicit = $model->explicit ? '1' : '0';

        $record->show_notes = $model->showNotes;

        if ($model->publishAt !== null) {
            $record->publish_at = $model->publishAt->format(
                DateTimeInterface::ATOM
            );
        }

        $record->is_published = $model->isPublished ? '1' : '0';

        $record->number = $model->number;

        $record->display_order = $model->displayOrder;

        $record->created_at = $model->createdAt->format(
            DateTimeInterface::ATOM
        );

        return $record;
    }
}
