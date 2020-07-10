<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Response;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Response;
use Psr\Http\Message\UriInterface;

class RedirectResponse extends Response
{
    public function __construct($uri, int $statusCode = 302, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        if (!is_string($uri) and !$uri instanceof UriInterface) {
            throw new InvalidArgumentException('Uri must be a string or an instance of UriInterface');
        }

        parent::__construct($statusCode, $headers + [
            'Location' => (string) $uri
        ], '', $version, $reason);
    }
}