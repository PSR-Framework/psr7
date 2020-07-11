<?php

declare(strict_types=1);

namespace Furious\Psr7\Response;

use Furious\Psr7\Response;

class JsonResponse extends Response
{
    public function __construct(array $data, int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        $body = json_encode($data);
        parent::__construct($statusCode, $headers + [
            'Content-Type' => 'application/json'
        ], $body, $version, $reason);
    }
}