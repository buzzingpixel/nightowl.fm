<?php

declare(strict_types=1);

namespace App\Http\Utilities\Segments;

use Psr\Http\Message\UriInterface;

use function array_slice;
use function array_values;
use function count;
use function explode;

class ExtractUriSegments
{
    public function __invoke(UriInterface $uri): UriSegments
    {
        return $this->extract($uri);
    }

    public function extract(UriInterface $uri): UriSegments
    {
        /** @phpstan-ignore-next-line */
        $segments = explode('/', $uri->getPath());

        foreach ($segments as $key => $val) {
            if ($val !== '') {
                continue;
            }

            unset($segments[$key]);
        }

        $segments = array_values($segments);

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
