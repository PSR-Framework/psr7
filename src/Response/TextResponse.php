<?php

declare(strict_types=1);

namespace Furious\Psr7\Response;

use Furious\Psr7\Response;

class TextResponse extends Response
{
    public function __construct(string $text, int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        parent::__construct($statusCode, $headers + [
            'Content-Type' => 'text/plain; charset=utf-8'
        ], $text, $version, $reason);
    }
}