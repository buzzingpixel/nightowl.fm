<?php

declare(strict_types=1);

namespace App\Context\Settings\Models;

class SettingModel
{
    public string $id = '';

    public string $key = '';

    /** @var mixed[] */
    public array $value = [];
}
