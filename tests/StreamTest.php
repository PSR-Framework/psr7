<?php

declare(strict_types=1);

namespace Tests\Furious\Psr7;

use Exception;
use PHPUnit\Framework\TestCase;
use Furious\Psr7\Stream;

class StreamTest extends TestCase
{
    public function testConstructor(): void
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());
        $stream->close();
    }

    public function testStreamCloses(): void
    {
        $handle = fopen('php://temp', 'r');
        $stream = Stream::new($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testConvertsToString(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testGetsContents(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
    }

    public function testChecksEof(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertFalse($stream->eof());
        $stream->read(4);
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    public function testGetSize(): void
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');
        $stream = Stream::new($handle);
        $this->assertEquals($size, $stream->getSize());

        $this->assertEquals($size, $stream->getSize());
        $stream->close();
    }

    public function testEnsuresSizeIsConsistent(): void
    {
        $h = fopen('php://temp', 'w+');
        $this->assertEquals(3, fwrite($h, 'foo'));
        $stream = Stream::new($h);
        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(4, $stream->write('test'));
        $this->assertEquals(7, $stream->getSize());
        $this->assertEquals(7, $stream->getSize());
        $stream->close();
    }

    public function testProvidesStreamPosition(): void
    {
        $handle = fopen('php://temp', 'w+');
        $stream = Stream::new($handle);
        $this->assertEquals(0, $stream->tell());
        $stream->write('foo');
        $this->assertEquals(3, $stream->tell());
        $stream->seek(1);
        $this->assertEquals(1, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());
        $stream->close();
    }

    public function testCanDetachStream(): void
    {
        $r = fopen('php://temp', 'w+');
        $stream = Stream::new($r);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($r, $stream->detach());
        $stream->detach();

        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());

        $throws = function (callable $fn) use ($stream) {
            try {
                $fn($stream);
                $this->fail();
            } catch (Exception $e) {

            }
        };

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->read(10);
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->write('bar');
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->seek(10);
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->tell();
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->eof();
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->getSize();
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            $stream->getContents();
        });

        $throws(function ($stream) {
            /** @var Stream $stream */
            (string) $stream;
        });

        $stream->close();
    }

    public function testCloseProperties(): void
    {
        $handle = fopen('php://temp', 'r+');
        $stream = Stream::new($handle);
        $stream->close();

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertEmpty($stream->getMetadata());
    }
}