<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\UriFactory;
use Arslanoov\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

final class UriFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $uri = (new UriFactory())->createUri($uri = '/uri');

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertInstanceOf(Uri::class, $uri);
    }
}