<?php

declare(strict_types=1);

namespace Furious\Psr7\Filter;

use Furious\Psr7\Exception\InvalidArgumentException;
use Furious\Psr7\Exception\InvalidPortException;
use function is_string;
use function preg_replace_callback;
use function rawurlencode;

final class UriFilter
{
    private const SCENARIOS = [
        'http' => 80,
        'https' => 443
    ];
    private const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
    private const CHAR_SUB_DELIMITERS = '!\$&\'\(\)\*\+,;=';

    public function filterPort(string $scheme, ?int $port): ?int
    {
        if (null === $port) {
            return null;
        }

        $port = (int) $port;
        if (0 > $port or 0xffff < $port) {
            throw new InvalidPortException(sprintf('Invalid port: %d. Must be between 0 and 65535', $port));
        }

        return $this->isNonStandardPort($scheme, $port) ? $port : null;
    }

    public function filterPath($path): string
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }

        return preg_replace_callback('/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'rawUrlEncodeMatchZero'], $path);
    }

    public function filterQuery($string): string
    {
        if (!is_string($string)) {
            throw new InvalidPortException('Query must be a string');
        }

        return preg_replace_callback('/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'rawUrlEncodeMatchZero'], $string);
    }

    public function filterFragment($string): string
    {
        if (!is_string($string)) {
            throw new InvalidPortException('Fragment must be a string');
        }

        return preg_replace_callback('/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/', [__CLASS__, 'rawUrlEncodeMatchZero'], $string);
    }

    private function isNonStandardPort(string $scheme, int $port): bool
    {
        return
            !isset(self::SCENARIOS[$scheme]) or
            self::SCENARIOS[$scheme] !== $port;
    }

    private function rawUrlEncodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }
}