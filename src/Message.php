<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Header\HeaderTrimmer;
use Arslanoov\Psr7\Header\HeaderValidator;
use Arslanoov\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Arslanoov\Psr7\Exception\InvalidArgumentException;
use function in_array;
use function mb_strtolower;
use function implode;
use function is_integer;
use function array_merge;

class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';
    protected array $headers = [];
    protected array $headerNames = [];
    protected ?StreamInterface $stream = null;

    // Protocol version

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        $message = clone $this;
        $message->protocolVersion = $version;
        return $message;
    }

    // Headers

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        $header = mb_strtolower($name);
        return isset($this->headerNames[$header]);
    }

    public function getHeader($name): array
    {
        $header = mb_strtolower($name);
        if ($this->hasHeader($header)) {
            $header = $this->headerNames[$header];
            return $this->headers[$header];
        }

        return [];
    }

    public function getHeaderLine($name): string
    {
        $header = $this->getHeader($name);
        return implode(', ', $header);
    }

    public function withHeader($name, $value): self
    {
        (new HeaderValidator())->validate($name, $value);
        $value = (new HeaderTrimmer())->trim($value);
        $header = mb_strtolower($name);

        $message = clone $this;
        if (isset($message->headerNames[$header])) {
            unset($message->headers[$message->headerNames[$header]]);
        }

        $message->headerNames[$header] = $header;
        $message->headers[$header] = $value;

        return $message;
    }

    public function withAddedHeader($name, $value): self
    {
        if (!is_string($name) or empty($name)) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        $message = clone $this;
        $message->setHeaders([
            $name => $value
        ]);

        return $message;
    }

    public function withoutHeader($name): self
    {
        $header = mb_strtolower($name);
        if (!$this->hasHeader($header)) {
            return $this;
        }

        $header = $this->headerNames[$header];

        $message = clone $this;

        unset($message->headers[$header]);
        unset($message->headerNames[$header]);

        return $message;
    }

    // Body

    public function getBody(): StreamInterface
    {
        if (null === $this->stream) {
            $this->stream = Stream::new();
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body): self
    {
        $message = clone $this;
        $message->stream = $body;
        return $message;
    }

    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $header => $value) {
            if (is_integer($header)) {
                $header = (string) $header;
            }

            (new HeaderValidator())->validate($header, $value);
            $value = (new HeaderTrimmer())->trim($value);
            $lowerHeader = mb_strtolower($header);

            if ($this->hasHeader($lowerHeader)) {
                $header = $this->headerNames[$lowerHeader];
                $this->headers[$header] = array_merge($this->getHeader($header), $value);
            } else {
                $this->headerNames[$lowerHeader] = $header;
                $this->headers[$header] = $value;
            }
        }
    }
}