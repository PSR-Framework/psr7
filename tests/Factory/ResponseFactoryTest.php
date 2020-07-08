<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\ResponseFactory;
use Arslanoov\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    public function testSuccess(): void
    {
        $request =
            (new ResponseFactory())
            ->createResponse($code = 500, $message = 'error')
        ;

        $this->assertInstanceOf(Response::class, $request);
        $this->assertInstanceOf(ResponseInterface::class, $request);
        $this->assertSame($request->getStatusCode(), $code);
        $this->assertSame($request->getReasonPhrase(), $message);
    }
}