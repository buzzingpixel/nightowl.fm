<?php

declare(strict_types=1);

namespace App\Context\Email\Services;

use App\Context\Email\Models\EmailModel;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;

class QueueEmail
{
    private QueueApi $queueApi;

    public function __construct(QueueApi $queueApi)
    {
        $this->queueApi = $queueApi;
    }

    public function __invoke(EmailModel $emailModel): void
    {
        $queueModel              = new QueueModel();
        $queueModel->handle      = 'send-email';
        $queueModel->displayName = 'Send Email';

        $queueItem          = new QueueItemModel();
        $queueItem->class   = SendQueueEmail::class;
        $queueItem->context = ['model' => $emailModel];

        $queueModel->addItem($queueItem);

        $this->queueApi->addToQueue($queueModel);
    }
}
