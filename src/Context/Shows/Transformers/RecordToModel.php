<?php

declare(strict_types=1);

namespace App\Context\Shows\Transformers;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\Shows\ShowRecord;

use function array_map;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class RecordToModel
{
    /**
     * @param KeywordRecord[]        $keywords
     * @param PersonModel[]          $hosts
     * @param PodcastCategoryModel[] $categories
     */
    public function transform(
        ShowRecord $record,
        array $keywords = [],
        array $hosts = [],
        array $categories = []
    ): ShowModel {
        $model = new ShowModel();

        $model->id = $record->id;

        $model->title = $record->title;

        $model->slug = $record->slug;

        $model->artworkFileLocation = $record->artwork_file_location;

        $model->description = $record->description;

        $model->status = $record->status;

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

        $model->itunesLink = $record->itunes_link;

        $model->googlePlayLink = $record->google_play_link;

        $model->stitcherLink = $record->stitcher_link;

        $model->spotifyLink = $record->spotify_link;

        $model->patreonLink = $record->patreon_link;

        $model->patreonCta = $record->patreon_cta;

        $model->patreonHeadline = $record->patreon_headline;

        $model->patreonDescription = $record->patreon_description;

        $model->keywords = array_map(
            static function (
                KeywordRecord $keywordRecord
            ): KeywordModel {
                $keywordModel          = new KeywordModel();
                $keywordModel->id      = $keywordRecord->id;
                $keywordModel->keyword = $keywordRecord->keyword;

                return $keywordModel;
            },
            $keywords,
        );

        $model->hosts = $hosts;

        $model->podcastCategories = $categories;

        return $model;
    }
}
