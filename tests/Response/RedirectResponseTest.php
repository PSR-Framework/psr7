<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Response;

use Furious\Psr7\Exception\InvalidArgumentException;
use Furious\Psr7\Response\RedirectResponse;
use Furious\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testStringSuccess(): void
    {
        $response = new RedirectResponse($uri = 'http://www.newpage.com');
        $this->assertEquals($uri, $response->getHeaderLine('Location'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testObjectSuccess(): void
    {
        $response = new RedirectResponse($uri = new Uri('http://www.newpage.com'));
        $this->assertEquals((string) $uri, $response->getHeaderLine('Location'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUriInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Uri must be a string or an instance of UriInterface');
        $response = new RedirectResponse(true);
    }
}