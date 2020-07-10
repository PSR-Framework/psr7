<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Response;

use Arslanoov\Psr7\Response\HtmlResponse;
use PHPUnit\Framework\TestCase;

class HtmlResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $response = new HtmlResponse($html = '<html>some html</html>');

        $this->assertEquals($html, (string) $response->getBody());
        $this->assertEquals($response->getHeaderLine( 'Content-Type'), 'text/html; charset=utf-8');
    }
}