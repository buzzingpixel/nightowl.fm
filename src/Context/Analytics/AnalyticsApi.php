<?php

declare(strict_types=1);

namespace App\Context\Analytics;

use App\Context\Analytics\Models\AnalyticsModel;
use App\Context\Analytics\Models\UriStatsModel;
use App\Context\Analytics\Services\CreatePageView;
use App\Context\Analytics\Services\GetTotalPageViewsSince;
use App\Context\Analytics\Services\GetUniqueTotalVisitorsSince;
use App\Context\Analytics\Services\GetUriStatsSince;
use App\Payload\Payload;
use DateTimeImmutable;
use Psr\Container\ContainerInterface;

use function assert;

class AnalyticsApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function createPageView(AnalyticsModel $model): Payload
    {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(CreatePageView::class);

        assert($service instanceof CreatePageView);

        return $service($model);
    }

    public function getTotalPageViewsSince(?DateTimeImmutable $date = null): int
    {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(GetTotalPageViewsSince::class);

        assert($service instanceof GetTotalPageViewsSince);

        return $service($date);
    }

    public function getUniqueTotalVisitorsSince(
        ?DateTimeImmutable $date = null
    ): int {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(GetUniqueTotalVisitorsSince::class);

        assert($service instanceof GetUniqueTotalVisitorsSince);

        return $service($date);
    }

    /**
     * @return UriStatsModel[]
     */
    public function getUriStatsSince(
        ?DateTimeImmutable $date = null
    ): array {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(GetUriStatsSince::class);

        assert($service instanceof GetUriStatsSince);

        return $service($date);
    }
}
