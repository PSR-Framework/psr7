<?php

declare(strict_types=1);

namespace Furious\Psr7\Protocol;

use Furious\Psr7\Exception\UnownedProtocolVersion;

final class Protocol
{
    public function getVersion(array $server): string
    {
        if (!isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (!preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnownedProtocolVersion(
                (string) $server['SERVER_PROTOCOL']
            );
        }

        return $matches['version'];
    }
}