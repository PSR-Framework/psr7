<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\ResponseFactory;
use Arslanoov\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    public function testDefault(): void
    {
        $response =
            (new ResponseFactory())
            ->createResponse($code = 500, $message = 'error')
        ;

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($response->getStatusCode(), $code);
        $this->assertSame($response->getReasonPhrase(), $message);
    }

    public function testJson(): void
    {
        $response =
            (new ResponseFactory())
            ->createJsonResponse([])
        ;

        $this->assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}