<?php

declare(strict_types=1);

namespace App\Http\Response\Shows;

use App\Context\Shows\Models\ShowModel;
use App\Http\Utilities\Segments\UriSegments;
use Psr\Http\Message\ResponseInterface;

use function dd;

class GetShowAction
{
    public function get(
        UriSegments $uriSegments,
        ShowModel $show
    ): ResponseInterface {
        dd('GetShowAction');
    }
}
