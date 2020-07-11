<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Factory;

use Furious\Psr7\Factory\UriFactory;
use Furious\Psr7\Uri;
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