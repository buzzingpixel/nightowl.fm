<?php

declare(strict_types=1);

namespace App\Context\TempFileStorage\Models;

use function Safe\json_encode;

class TempFileStorageModel
{
    public string $filePath = '';
    public string $fileName = '';

    public function __construct(
        string $filePath = '',
        string $fileName = ''
    ) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    public function toJson(): string
    {
        return json_encode([
            'filePath' => $this->filePath,
            'fileName' => $this->fileName,
        ]);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
