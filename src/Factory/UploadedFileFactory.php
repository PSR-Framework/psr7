<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use const UPLOAD_ERR_OK;

final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        StreamInterface $stream, int $size = null, int $error = UPLOAD_ERR_OK,
        string $clientFilename = null, string $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile(
            $stream, $size ?? $stream->getSize(), $error,
            $clientFilename, $clientMediaType
        );
    }
}