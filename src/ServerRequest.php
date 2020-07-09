<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use function array_key_exists;
use function is_array;

final class ServerRequest extends Request implements ServerRequestInterface
{
    private array $queryParams = [];
    private array $cookieParams = [];
    /** @var array|UploadedFileInterface[] */
    private array $uploadedFiles = [];
    private array $attributes = [];
    private array $serverParams;
    /** @var array|object|null */
    private $parsedBody;

    public function __construct(
        string $method, $uri, string $version = '1.1', array $headers = [],
        array $queryParams = [], $body = null,
        array $serverParams = [], array $cookieParams = [],
        array $files = [], array $attributes = []
    )
    {
        parent::__construct($method, $uri, $headers, $body, $version);
        if (is_string($body)) {
            $body = Stream::new($body);
        }
        $this->queryParams = $queryParams;
        $this->validateBody($body);
        $this->parsedBody = $body;
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->uploadedFiles =  $files;
        $this->attributes = $attributes;
    }
    
    // Get

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function getAttribute($attribute, $default = null)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    // With
    
    public function withUploadedFiles(array $uploadedFiles)
    {
        $serverRequest = clone $this;
        $serverRequest->uploadedFiles = $uploadedFiles;
        return $serverRequest;
    }

    public function withCookieParams(array $cookies)
    {
        $serverRequest = clone $this;
        $serverRequest->cookieParams = $cookies;
        return $serverRequest;
    }

    public function withQueryParams(array $query)
    {
        $serverRequest = clone $this;
        $serverRequest->queryParams = $query;
        return $serverRequest;
    }

    public function withAttribute($attribute, $value): self
    {
        $serverRequest = clone $this;
        $serverRequest->attributes[$attribute] = $value;
        return $serverRequest;
    }

    public function withoutAttribute($attribute): self
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $serverRequest = clone $this;
        unset($serverRequest->attributes[$attribute]);
        return $serverRequest;
    }

    public function withParsedBody($data)
    {
        if (is_string($data)) {
            $data = Stream::new($data);
        }

        $this->validateBody($data);

        $serverRequest = clone $this;
        $serverRequest->parsedBody = $data;
        return $serverRequest;
    }

    private function validateBody($body): void
    {
        if (null !== $body and !is_array($body) and !$body instanceof StreamInterface) {
            throw new InvalidArgumentException('Body must be array, instance of StreamInterface or null');
        }
    }
}