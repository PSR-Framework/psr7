<?php

declare(strict_types=1);

namespace Furious\Psr7\Response;

use Furious\Psr7\Response;

class HtmlResponse extends Response
{
    public function __construct(string $html, int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        parent::__construct($statusCode, $headers + [
            'Content-Type' => 'text/html; charset=utf-8'
        ], $html, $version, $reason);
    }
}