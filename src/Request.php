<?php

declare(strict_types=1);

namespace Furious\Psr7;

use Furious\Psr7\Exception\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use function is_string;
use function preg_match;

class Request extends Message implements RequestInterface
{
    private const METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE',
        'HEAD', 'CONNECT', 'OPTIONS', 'TRACE'
    ];

    private string $method;
    private ?string $requestTarget = null;
    private UriInterface $uri;

    public function  __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1')
    {
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }
        if (!$this->isValidMethod($method)) {
            throw new InvalidArgumentException('Unsupported HTTP method');
        }

        $this->method = $method;
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocolVersion = $version;

        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ('' !== $body and null !== $body) {
            $this->stream = Stream::new($body);
        }
    }

    // Get

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestTarget(): string
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ('' === $target) {
            $target = '/';
        }

        if ('' !== $this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    // With

    public function withRequestTarget($requestTarget): self
    {
        if ($this->containWhitespace($requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $request = clone $this;
        $request->requestTarget = $requestTarget;

        return $request;
    }

    public function withMethod($method): self
    {
        if (!is_string($method) or !$this->isValidMethod($method)) {
            throw new InvalidArgumentException('Unsupported HTTP method');
        }

        $request = clone $this;
        $request->method = $method;

        return $request;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $request = clone $this;
        $request->uri = $uri;

        if (!$preserveHost or !$this->hasHeader('Host')) {
            $request->updateHostFromUri();
        }

        return $request;
    }

    // Update

    private function updateHostFromUri(): void
    {
        if ('' === $host = $this->uri->getHost()) {
            return;
        }

        if (null !== ($port = $this->uri->getPort())) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $header = 'Host';
            $this->headerNames['host'] = $header;
        }

        $this->headers = [$header => [$host]] + $this->headers;
    }

    // Contain

    private function containWhitespace(string $requestTarget): bool
    {
        return boolval(preg_match('#\s#', $requestTarget));
    }

    private function isValidMethod(string $method): bool
    {
        return in_array($method, self::METHODS);
    }
}