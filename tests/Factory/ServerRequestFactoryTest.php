<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Factory;

use Furious\Psr7\Factory\ServerRequestFactory;
use Furious\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $serverRequest = (new ServerRequestFactory())->createServerRequest('GET', '/home', [
            'foo' => 'bar'
        ]);

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
    }

    public function testFromGlobals(): void
    {
        $serverRequest = (new ServerRequestFactory)->fromGlobals();

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
    }
}