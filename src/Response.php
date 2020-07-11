<?php

declare(strict_types=1);

namespace Furious\Psr7;

use Furious\Psr7\Exception\InvalidArgumentException;
use Furious\Psr7\Response\Phrases;
use Psr\Http\Message\ResponseInterface;
use function is_int;
use function is_string;

class Response extends Message implements ResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase = '';

    public function __construct(
        int $statusCode = 200, array $headers = [], $body = null,
        string $version = '1.1', string $reason = null
    )
    {
        if ('' !== $body and null !== $body) {
            $this->stream = Stream::new($body);
        }

        $this->validateStatusCode($statusCode);
        $this->statusCode = $statusCode;
        $this->setHeaders($headers);
        $this->initializeReasonPhrase($reason);
        $this->protocolVersion = $version;
    }

    private function initializeReasonPhrase(?string $reason): void
    {
        if (null === $reason and isset(Phrases::LIST[$this->statusCode])) {
            $this->reasonPhrase = Phrases::LIST[$this->statusCode];
        } else {
            $this->reasonPhrase = $reason ?? '';
        }
    }

    // Get

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    // With

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->validateStatusCode($code);

        $response = clone $this;
        $response->statusCode = $code;

        if (
            (null === $reasonPhrase or '' === $reasonPhrase) and
            isset(Phrases::LIST[$response->statusCode])
        ) {
            $reasonPhrase = Phrases::LIST[$response->statusCode];
        }

        $response->reasonPhrase = $reasonPhrase;
        return $response;
    }

    private function validateStatusCode($code): void
    {
        if (!is_int($code) and !is_string($code)) {
            throw new InvalidArgumentException('Status code has to be an integer');
        }

        if ($code < 100 or $code > 599) {
            throw new InvalidArgumentException('Status code has to be an integer between 100 and 599');
        }
    }
}