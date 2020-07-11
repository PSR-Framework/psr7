<?php

declare(strict_types=1);

namespace Furious\Psr7\Header;

use function trim;

final class HeaderTrimmer
{
    public function trim($values): array
    {
        if (!is_array($values)) {
            $stringValues = (string) $values;
            $trimmedValues = $this->trimHeaderValue($stringValues);
            return [$trimmedValues];
        }

        return $this->trimHeaderValues($values);
    }

    private function trimHeaderValues(array $values): array
    {
        $trimmedValues = [];
        foreach ($values as $value) {
            $trimmedValues[] = $this->trimHeaderValue((string) $value);
        }

        return $trimmedValues;
    }

    private function trimHeaderValue(string $value): string
    {
        return trim($value, " \t");
    }
}