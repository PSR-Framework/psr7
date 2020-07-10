<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Response;

use Arslanoov\Psr7\Response;

final class TextResponse extends Response
{
    public function __construct(string $text, int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        parent::__construct($statusCode, $headers + [
            'Content-Type' => 'text/plain; charset=utf-8'
        ], $text, $version, $reason);
    }
}