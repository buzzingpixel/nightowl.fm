<?php

declare(strict_types=1);

namespace App\Context\Email\Interfaces;

use App\Context\Email\Models\EmailModel;
use App\Payload\Payload;

interface SendMailAdapter
{
    /**
     * Sends an email
     */
    public function __invoke(EmailModel $emailModel): Payload;
}
