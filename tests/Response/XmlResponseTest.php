<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Response;

use Furious\Psr7\Response\JsonResponse;
use Furious\Psr7\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class XmlResponseTest extends TestCase
{
    public function testObject(): void
    {
        $response = new XmlResponse($xml = new SimpleXMLElement('<hello>Hello!</hello>'));
        $this->assertEquals($xml->asXML(), (string) $response->getBody());
        $this->assertEquals($response->getHeaderLine( 'Content-Type'), 'application/xml');
    }

    public function testString(): void
    {
        $response = new XmlResponse($text = '<hello>Hello!</hello>');
        $this->assertEquals($text, (string) $response->getBody());
        $this->assertEquals($response->getHeaderLine( 'Content-Type'), 'application/xml');
    }
}