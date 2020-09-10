<?php

declare(strict_types=1);

namespace App\Context\Keywords;

use App\Context\Keywords\Models\FetchModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\Keywords\Services\FetchKeywords;
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

    /**
     * @return KeywordModel[]
     */
    public function fetchKeywords(?FetchModel $fetchModel): array
    {
        $service = $this->di->get(FetchKeywords::class);

        assert($service instanceof FetchKeywords);

        return $service->fetch($fetchModel);
    }

    public function fetchKeyword(?FetchModel $fetchModel): ?KeywordModel
    {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchKeywords($fetchModel)[0] ?? null;
    }
}
