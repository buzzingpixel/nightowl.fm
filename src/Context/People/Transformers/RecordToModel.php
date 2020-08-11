<?php

declare(strict_types=1);

namespace App\Context\People\Transformers;

use App\Context\Links\Models\LinkModel;
use App\Context\People\Models\PersonModel;
use App\Persistence\People\PersonRecord;

use function array_map;
use function Safe\json_decode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class RecordToModel
{
    public function __invoke(PersonRecord $record): PersonModel
    {
        return $this->transform($record);
    }

    public function transform(PersonRecord $record): PersonModel
    {
        $model = new PersonModel();

        $model->id = $record->id;

        $model->firstName = $record->first_name;

        $model->lastName = $record->last_name;

        $model->slug = $record->slug;

        $model->email = $record->email;

        $model->photoFileLocation = $record->photo_file_location;

        $model->photoPreference = $record->photo_preference;

        $model->bio = $record->bio;

        $model->location = $record->location;

        $model->facebookPageSlug = $record->facebook_page_slug;

        $model->twitterHandle = $record->twitter_handle;

        /** @psalm-suppress MixedAssignment */
        $linksArray = json_decode($record->links, true);

        /** @psalm-suppress MixedArgument */
        $model->links = array_map(
            /**
             * @param array<string, string> $linkArray
             */
            static fn (array $linkArray) => new LinkModel(
                $linkArray['title'],
                $linkArray['url'],
            ),
            $linksArray
        );

        return $model;
    }
}
