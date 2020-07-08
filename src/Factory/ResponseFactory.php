<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return
            (new Response())
            ->withStatus($code, $reasonPhrase)
        ;
    }
}