<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Header\HeadersCollection;
use Arslanoov\Psr7\Protocol\Protocol;
use Arslanoov\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri,'1.1', [], [], [], $serverParams);
    }

    public function fromGlobals(): ServerRequest
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $protocolVersion = (new Protocol())->getVersion($_SERVER);
        $headers = (new HeadersCollection($_SERVER))->get();
        $body = $_POST ?? 'php://input';

        return new ServerRequest(
            $method,
            $uri,
            $protocolVersion,
            $headers,
            $_GET,
            $body,
            $_SERVER,
            $_COOKIE,
            $_FILES, []
        );
    }
}