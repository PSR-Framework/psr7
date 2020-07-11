<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Factory;

use Furious\Psr7\Factory\ResponseFactory;
use Furious\Psr7\Response;
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

    public function testXml(): void
    {
        $response =
            (new ResponseFactory())
                ->createXmlResponse('<hello>Hello!!!</hello>')
        ;

        $this->assertInstanceOf(Response\XmlResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testEmpty(): void
    {
        $response =
            (new ResponseFactory())
                ->createEmptyResponse()
        ;

        $this->assertInstanceOf(Response\EmptyResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testText(): void
    {
        $response =
            (new ResponseFactory())
                ->createTextResponse('Hello!!!')
        ;

        $this->assertInstanceOf(Response\TextResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHtml(): void
    {
        $response =
            (new ResponseFactory())
                ->createHtmlResponse('<html>Some html</html>')
        ;

        $this->assertInstanceOf(Response\HtmlResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRedirect(): void
    {
        $response =
            (new ResponseFactory())
                ->createRedirectResponse($uri = 'http://somesite.com')
        ;

        $this->assertInstanceOf(Response\RedirectResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}