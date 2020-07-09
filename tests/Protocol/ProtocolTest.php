<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Protocol;

use Arslanoov\Psr7\Exception\UnownedProtocolVersion;
use Arslanoov\Psr7\Protocol\Protocol;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    public function testValidVersion(): void
    {
        $protocol = (new Protocol())->getVersion([
            'SERVER_PROTOCOL' => 'HTTP/1.1'
        ]);

        $this->assertEquals($protocol, '1.1');
    }

    public function testInvalidVersion(): void
    {
        $this->expectException(UnownedProtocolVersion::class);
        $this->expectExceptionMessage($version = 'ghgewvdnifh32ewb');

        (new Protocol())->getVersion([
            'SERVER_PROTOCOL' => $version
        ]);
    }
}