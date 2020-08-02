<?php

declare(strict_types=1);

namespace App\Context\Email\Models;

class EmailModel
{
    public string $fromEmail = '';

    public string $toName = '';

    public string $toEmail = '';

    public string $subject = '';

    public string $plainText = '';

    public string $html = '';
}
