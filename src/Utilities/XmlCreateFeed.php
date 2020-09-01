<?php

declare(strict_types=1);

namespace App\Utilities;

use SimpleXMLElement;

use function implode;

use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_NONE;
use const LIBXML_NOERROR;

class XmlCreateFeed
{
    public function create(): SimpleXMLElement
    {
        $nameSpaces = [
            'xmlns:dc="http://purl.org/dc/elements/1.1/"',
            'xmlns:dcterms="https://purl.org/dc/terms"',
            'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"',
            'xmlns:admin="http://webns.net/mvcb/"',
            'xmlns:atom="http://www.w3.org/2005/Atom/"',
            'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"',
            'xmlns:content="http://purl.org/rss/1.0/modules/content/"',
            'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"',
            'xmlns:spotify="http://www.spotify.com/ns/rss"',
            'xmlns:psc="https://podlove.org/simple-chapters/"',
        ];

        return new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>' .
            '<rss version="2.0" ' . implode(' ', $nameSpaces) . ' />',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL
        );
    }
}
