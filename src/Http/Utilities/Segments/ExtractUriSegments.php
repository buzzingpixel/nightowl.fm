<?php

declare(strict_types=1);

namespace App\Http\Utilities\Segments;

use Psr\Http\Message\UriInterface;

use function array_filter;
use function array_slice;
use function array_values;
use function count;
use function explode;

class ExtractUriSegments
{
    public function __invoke(UriInterface $uri): UriSegments
    {
        /** @phpstan-ignore-next-line */
        $segments = array_values(array_filter(
            explode('/', $uri->getPath()),
            static function ($item): string {
                return $item;
            }
        ));

        $segmentsSansPagination = $segments;

        $pageNum = 1;

        $segmentsCount = count($segments);

        if ($segmentsCount > 1 && $segments[$segmentsCount - 2] === 'page') {
            $pageNum = (int) $segments[$segmentsCount - 1];

            $segmentsSansPagination = array_slice(
                $segments,
                0,
                $segmentsCount - 2
            );
        }

        return new UriSegments(
            $segments,
            $segmentsSansPagination,
            $pageNum
        );
    }
}
