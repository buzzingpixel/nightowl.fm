<?php

declare(strict_types=1);

namespace App\Context\Analytics\Transformers;

use App\Context\Analytics\Models\AnalyticsModel;
use App\Persistence\Analytics\AnalyticsRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class AnalyticsModelToRecord
{
    public function __invoke(AnalyticsModel $model): AnalyticsRecord
    {
        $record = new AnalyticsRecord();

        $record->cookie_id = $model->cookie->value();

        if ($model->user !== null) {
            $record->user_id = $model->user->id;
        }

        $record->logged_in_on_page_load = $model->wasLoggedInOnPageLoad ?
            '1' :
            '0';

        $record->uri = $model->uri;

        /** @noinspection PhpUnhandledExceptionInspection */
        $record->date = $model->date->format(
            DateTimeInterface::ATOM
        );

        return $record;
    }
}
