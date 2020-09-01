<?php

declare(strict_types=1);

namespace App\Utilities;

use DOMDocument;
use SimpleXMLElement;

use function dom_import_simplexml;

class XmlRenderFeed
{
    public function render(SimpleXMLElement $xml): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $dom->appendChild(
            $dom->importNode(
                dom_import_simplexml($xml),
                true
            )
        );

        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
