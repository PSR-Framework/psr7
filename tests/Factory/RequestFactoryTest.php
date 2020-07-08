<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\RequestFactory;
use Arslanoov\Psr7\Request;
use Arslanoov\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $request = (new RequestFactory())->createRequest($method = 'GET', $uri = new Uri('/home'));

        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame($request->getMethod(), $method);
        $this->assertSame($request->getUri(), $uri);
    }
}