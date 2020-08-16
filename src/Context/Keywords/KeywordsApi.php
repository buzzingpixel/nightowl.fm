<?php

declare(strict_types=1);

namespace App\Context\Keywords;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\Keywords\Services\SaveKeyword;
use Psr\Container\ContainerInterface;

use function assert;

class KeywordsApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveKeyword(KeywordModel $keyword): void
    {
        $service = $this->di->get(SaveKeyword::class);

        assert($service instanceof SaveKeyword);

        $service->save($keyword);
    }
}
