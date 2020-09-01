<?php

declare(strict_types=1);

namespace App\Utilities;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;

use function assert;
use function dom_import_simplexml;

class XmlRenderFeed
{
    public function render(SimpleXMLElement $xml): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $domElement = dom_import_simplexml($xml);

        assert($domElement instanceof DOMElement);

        $dom->appendChild(
            $dom->importNode($domElement, true)
        );

        $dom->formatOutput = true;

        return (string) $dom->saveXML();
    }
}
