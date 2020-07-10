<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Header;

final class HeadersCollection
{
    private array $serverParameters;

    /**
     * HeadersCollection constructor.
     * @param array $serverParameters
     */
    public function __construct(array $serverParameters)
    {
        $this->serverParameters = $serverParameters;
    }

    public function get(): array
    {
        $headers = [];
        foreach ($this->serverParameters as $key => $value) {
            if (!is_string($key) or $value === '') {
                continue;
            }

            if ($name = $this->getHeaderName($key)) {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    private function getHeaderName(string $key): ?string
    {
        $name = null;

        if ($this->isRedirectHeader($key)) {
            $key = substr($key, 9);
        }

        if ($this->isContentHeader($key)) {
            $name = strtr(strtolower($key), '_', '-');
        }

        if ($this->isHttpHeader($key)) {
            $name = strtr(strtolower(substr($key, 5)), '_', '-');
        }

        return $name;
    }

    private function isRedirectHeader(string $key): bool
    {
        return strpos($key, 'REDIRECT_') === 0;
    }

    private function isContentHeader(string $key): bool
    {
        return strpos($key, 'CONTENT_') === 0;
    }

    private function isHttpHeader(string $key): bool
    {
        return strpos($key, 'HTTP_') === 0;
    }
}