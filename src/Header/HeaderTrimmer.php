<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Header;

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

        $returnValues = [];
        foreach ($values as $value) {
            $returnValues[] = $this->trimHeaderValue($value);
        }

        return $values;
    }

    private function trimHeaderValue(string $value): string
    {
        return trim($value, " \t");
    }
}