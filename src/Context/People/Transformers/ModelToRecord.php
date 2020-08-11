<?php

declare(strict_types=1);

namespace App\Context\People\Transformers;

use App\Context\Links\Models\LinkModel;
use App\Context\People\Models\PersonModel;
use App\Persistence\People\PersonRecord;

use function array_map;
use function Safe\json_encode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class ModelToRecord
{
    public function __invoke(PersonModel $model): PersonRecord
    {
        return $this->transform($model);
    }

    public function transform(PersonModel $model): PersonRecord
    {
        $record = new PersonRecord();

        $record->id = $model->id;

        $record->first_name = $model->firstName;

        $record->last_name = $model->lastName;

        $record->slug = $model->slug;

        $record->email = $model->email;

        $record->photo_file_location = $model->photoFileLocation;

        $record->photo_preference = $model->photoPreference;

        $record->bio = $model->bio;

        $record->location = $model->location;

        $record->facebook_page_slug = $model->facebookPageSlug;

        $record->twitter_handle = $model->twitterHandle;

        $links = $model->links;

        $record->links = json_encode(array_map(
            static fn (LinkModel $link) => [
                'title' => $link->title,
                'url' => $link->url,
            ],
            $links,
        ));

        return $record;
    }
}
