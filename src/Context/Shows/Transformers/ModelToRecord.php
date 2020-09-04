<?php

declare(strict_types=1);

namespace App\Context\Shows\Transformers;

use App\Context\Shows\Models\ShowModel;
use App\Persistence\Shows\ShowRecord;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class ModelToRecord
{
    public function transform(ShowModel $model): ShowRecord
    {
        $record = new ShowRecord();

        $record->id = $model->id;

        $record->title = $model->title;

        $record->slug = $model->slug;

        $record->artwork_file_location = $model->artworkFileLocation;

        $record->status = $model->status;

        $record->description = $model->description;

        $record->explicit = $model->explicit ? '1' : '0';

        $record->itunes_link = $model->itunesLink;

        $record->google_play_link = $model->googlePlayLink;

        $record->stitcher_link = $model->stitcherLink;

        $record->spotify_link = $model->spotifyLink;

        return $record;
    }
}
