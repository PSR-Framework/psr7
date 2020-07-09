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

            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                if (array_key_exists($key, $this->serverParameters)) {
                    continue;
                }
            }

            if (strpos($key, 'CONTENT_') === 0) {
                $name = strtr(strtolower($key), '_', '-');
                $headers[$name] = $value;
                continue;
            }

            if (strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }
        }

        return $headers;
    }
}