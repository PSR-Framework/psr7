<?php

declare(strict_types=1);

namespace Furious\Psr7\Factory;

use Furious\Psr7\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response())
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createJsonResponse(array $data, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response\JsonResponse($data))
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createXmlResponse($xml, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response\XmlResponse($xml))
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createTextResponse(string $text, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response\TextResponse($text))
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createHtmlResponse(string $html, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response\HtmlResponse($html))
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createRedirectResponse($uri, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response\RedirectResponse($uri))
            ->withStatus($code, $reasonPhrase)
        ;
    }

    public function createEmptyResponse(): ResponseInterface
    {
        return (new Response\EmptyResponse());
    }
}