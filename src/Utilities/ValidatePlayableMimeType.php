<?php

declare(strict_types=1);

namespace App\Utilities;

use Config\General;
use getID3;

class ValidatePlayableMimeType
{
    private General $generalConfig;
    private getID3 $getID3;

    public function __construct(
        General $generalConfig,
        getID3 $getID3
    ) {
        $this->generalConfig = $generalConfig;
        $this->getID3        = $getID3;
    }

    public function fromTempPath(string $path): bool
    {
        $directory = $this->generalConfig->pathToStorageDirectory();

        $directory .= '/temp/';

        $fullPath = $directory . $path;

        $mp3Info = $this->getID3->analyze($fullPath);

        return ($mp3Info['fileformat'] ?? '') === 'mp3';
    }
}
