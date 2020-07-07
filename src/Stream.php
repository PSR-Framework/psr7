<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\IncorrectStreamPositionException;
use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\NotReadableStreamException;
use Arslanoov\Psr7\Exception\NotSeekableStreamException;
use Arslanoov\Psr7\Exception\NotWritableStreamException;
use Arslanoov\Psr7\Exception\UnableToSeekException;
use Psr\Http\Message\StreamInterface;
use const SEEK_CUR;
use function is_string;
use function fwrite;
use function is_resource;
use function fopen;
use function fseek;
use function fread;
use function stream_get_meta_data;
use function clearstatcache;
use function fstat;
use function ftell;
use function feof;
use function stream_get_contents;

final class Stream implements StreamInterface
{
    private bool $seekable;
    private bool $readable;
    private bool $writable;
    /** @var mixed */
    private $uri = null;
    private ?int $size = null;
    /** @var resource|null */
    private $stream;

    private const READ_HASH = [
        'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
        'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
        'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a+' => true,
    ];

    private const WRITE_HASH = [
        'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
        'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
        'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
    ];

    public static function new($body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body)) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, $body);
            $body = $resource;
        }

        if (is_resource($body)) {
            $new = new self();
            $new->stream = $body;
            $meta = stream_get_meta_data($new->stream);
            $new->seekable = $meta['seekable'] and !fseek($new->stream, 0, SEEK_CUR);
            $new->readable = isset(self::READ_HASH[$meta['mode']]);
            $new->writable = isset(self::WRITE_HASH[$meta['mode']]);
            $new->uri = $new->getMetadata('uri');

            return $new;
        }

        throw new InvalidArgumentException('Body must be a string, resource or an instance of StreamInterface.');
    }

    public function __toString()
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);

        $this->size = null;
        $this->uri = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (false === $result = ftell($this->stream)) {
            throw new IncorrectStreamPositionException();
        }

        return $result;
    }

    public function read($length): string
    {
        if (!$this->readable) {
            throw new NotReadableStreamException();
        }

        return fread($this->stream, $length);
    }


    public function eof(): bool
    {
        return !$this->stream or feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new NotSeekableStreamException();
        }

        if (-1 === fseek($this->stream, $offset, $whence)) {
            throw new UnableToSeekException($offset, $whence);
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function write($string): int
    {
        if (!$this->writable) {
            throw new NotWritableStreamException('Cannot write to a non-writable stream');
        }

        $this->size = null;

        $result = fwrite($this->stream, $string);
        if (false === $result) {
            throw new NotWritableStreamException();
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new NotReadableStreamException();
        }

        $contents = stream_get_contents($this->stream);
        if (false === $contents) {
            throw new NotReadableStreamException();
        }

        return $contents;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream);

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}