<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Response;

use Furious\Psr7\Response\TextResponse;
use PHPUnit\Framework\TestCase;

class TextResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $response = new TextResponse($text = 'some text');

        $this->assertEquals($text, (string) $response->getBody());
        $this->assertEquals($response->getHeaderLine( 'Content-Type'), 'text/plain; charset=utf-8');
    }
}