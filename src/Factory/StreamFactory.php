<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\RuntimeException;
use Arslanoov\Psr7\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function fopen;
use function in_array;

final class StreamFactory implements StreamFactoryInterface
{
    private const MODES = [
        'r', 'w', 'a', 'x', 'c'
    ];

    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::new($content);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::new($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);
        if (false === $resource) {
            if ('' === $mode or false === in_array($mode[0], self::MODES)) {
                throw new InvalidArgumentException('The mode ' . $mode . ' is invalid.');
            }

            throw new RuntimeException('The file ' . $filename . ' cannot be opened.');
        }

        return Stream::new($resource);
    }
}