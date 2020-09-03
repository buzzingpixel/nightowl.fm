<?php

declare(strict_types=1);

namespace App\Context\Pages;

use App\Context\Pages\Models\FetchModel;
use App\Context\Pages\Models\PageModel;
use App\Context\Pages\Services\DeletePage;
use App\Context\Pages\Services\FetchPages;
use App\Context\Pages\Services\SavePage;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class PagesApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function savePage(PageModel $page): Payload
    {
        $service = $this->di->get(SavePage::class);

        assert($service instanceof SavePage);

        return $service->save($page);
    }

    public function deletePage(PageModel $page): Payload
    {
        $service = $this->di->get(DeletePage::class);

        assert($service instanceof DeletePage);

        return $service->delete($page);
    }

    /**
     * @return PageModel[]
     */
    public function fetchPages(?FetchModel $fetchModel = null): array
    {
        $service = $this->di->get(FetchPages::class);

        assert($service instanceof FetchPages);

        return $service->fetch($fetchModel);
    }

    public function fetchPage(?FetchModel $fetchModel = null): ?PageModel
    {
        $fetchModel ??= new FetchModel();

        $fetchModel->limit = 1;

        return $this->fetchPages($fetchModel)[0] ?? null;
    }
}
