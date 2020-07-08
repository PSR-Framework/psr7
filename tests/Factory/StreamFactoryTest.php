<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\StreamFactory;
use Arslanoov\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactoryTest extends TestCase
{
    private StreamFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = new StreamFactory();
    }

    public function testCreate(): void
    {
        $stream = $this->factory->createStream('content');

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testFromResource(): void
    {
        $r = fopen('php://temp', 'w+');
        $stream = $this->factory->createStreamFromResource($r);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testFromFile(): void
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');
        $stream = $this->factory->createStreamFromResource($handle);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }
}