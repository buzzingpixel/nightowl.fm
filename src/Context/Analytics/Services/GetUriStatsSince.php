<?php

declare(strict_types=1);

namespace App\Context\Analytics\Services;

use App\Context\Analytics\Models\UriStatsModel;
use App\Persistence\Analytics\AnalyticsRecord;
use App\Persistence\Constants;
use App\Persistence\RecordQueryFactory;
use DateTimeImmutable;

use function array_values;
use function count;
use function Safe\ksort;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class GetUriStatsSince
{
    private RecordQueryFactory $queryFactory;

    public function __construct(RecordQueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * @return UriStatsModel[]
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function __invoke(?DateTimeImmutable $date = null): array
    {
        $query = ($this->queryFactory)(new AnalyticsRecord());

        if ($date !== null) {
            $query = $query->withWhere(
                'date',
                $date->format(Constants::POSTGRES_OUTPUT_FORMAT),
                '>'
            );
        }

        /** @var AnalyticsRecord[] $records */
        $records = $query->all();

        if (count($records) < 1) {
            return [];
        }

        $uriStatsModels = [];

        $visitors = [];

        foreach ($records as $record) {
            if (! isset($uriStatsModels[$record->uri])) {
                $uriModel = new UriStatsModel();

                $uriModel->uri = $record->uri;

                $visitors[$record->uri] = [];

                $uriStatsModels[$record->uri] = $uriModel;
            }

            if (! isset($visitors[$record->uri][$record->cookie_id])) {
                $visitors[$record->uri][$record->cookie_id] = $record->cookie_id;

                $uriStatsModels[$record->uri]->totalUniqueVisitorsInTimeRange += 1;
            }

            $uriStatsModels[$record->uri]->totalVisitorsInTimeRange += 1;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        ksort($uriStatsModels);

        /** @psalm-suppress MixedReturnTypeCoercion */
        return array_values($uriStatsModels);
    }
}
