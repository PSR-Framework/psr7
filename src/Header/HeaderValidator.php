<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Header;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use function preg_match;
use function is_string;
use function is_array;
use function is_numeric;

final class HeaderValidator
{
    public function validate(string $header, $values): void
    {
        $this->validateHeaderMatchRfc($header);

        if (!is_array($values)) {
            $this->validateHeaderValueMatchRfc($values);
            return;
        }

        $this->validateHeaderValuesEmpty($values);

        foreach ($values as $value) {
            $this->validateHeaderValueMatchRfc($value);
        }
    }

    // Validate

    private function validateHeaderMatchRfc(string $header): void
    {
        if (!$this->matchHeaderRfc($header)) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }
    }

    private function validateHeaderValueMatchRfc($value): void
    {
        if (
            (!is_numeric($value) and !is_string($value))
            or !$this->matchHeaderValuesRfc((string) $value)
        ) {
            throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
        }
    }

    private function validateHeaderValuesEmpty($values): void
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Header values must be a string or an array of strings, empty array given.');
        }
    }

    // Match

    private function matchHeaderRfc(string $header): bool
    {
        return boolval(preg_match("@^[!#$&%'+*.^_`|~0-9A-Za-z-]+$@", $header));
    }

    private function matchHeaderValuesRfc(string $values): bool
    {
        return boolval(preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", $values));
    }
}