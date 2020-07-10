<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Response;

use Arslanoov\Psr7\Response;
use SimpleXMLElement;

class XmlResponse extends Response
{
    public function __construct($xml, int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        if ($xml instanceof SimpleXMLElement) {
            $body = $xml->asXML();
        } else {
            $body = (string) $xml;
        }

        parent::__construct($statusCode, $headers + [
            'Content-Type' => 'application/xml'
        ], $body, $version, $reason);
    }
}