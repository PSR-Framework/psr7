<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Response;

use Arslanoov\Psr7\Response;

class EmptyResponse extends Response
{
    public function __construct(array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        parent::__construct(204, $headers, '', $version, $reason);
    }
}