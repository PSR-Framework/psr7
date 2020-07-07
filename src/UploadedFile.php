<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\InvalidUploadErrorException;
use Arslanoov\Psr7\Exception\RuntimeException;
use Arslanoov\Psr7\Exception\StreamAlreadyMovedException;
use Arslanoov\Psr7\Exception\UploadErrorException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use function fopen;
use function is_resource;
use function is_string;
use function move_uploaded_file;
use function rename;
use function sprintf;
use const PHP_SAPI;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

final class UploadedFile implements UploadedFileInterface
{
    private int $error;
    private int $size;
    private bool $moved = false;
    private ?string $file = null;
    private ?string $clientFileName = null;
    private ?string $clientMediaType = null;
    private ?StreamInterface $stream = null;

    private const ERROR_MESSAGES = [
        UPLOAD_ERR_OK         => 'The file successfully uploaded.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temp folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];

    public function __construct(
        $streamOrFile, int $size, int $error,
        string $clientFileName = null, string $clientMediaType = null
    )
    {
        if ($error < 0 or $error > 8) {
            throw new InvalidUploadErrorException('Invalid error status for UploadedFile');
        }

        if (UPLOAD_ERR_OK !== $error) {
            throw new UploadErrorException(self::ERROR_MESSAGES[$this->error]);
        }

        $this->error = $error;
        $this->size = $size;
        $this->clientFileName = $clientFileName;
        $this->clientMediaType = $clientMediaType;

        if (UPLOAD_ERR_OK === $this->error) {
            if (is_string($streamOrFile)) {
                $this->file = $streamOrFile;
            } elseif (is_resource($streamOrFile)) {
                $this->stream = Stream::new($streamOrFile);
            } elseif ($streamOrFile instanceof StreamInterface) {
                $this->stream = $streamOrFile;
            } else {
                throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
            }
        }
    }

    // Get

    public function getSize(): int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFileName;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    public function getStream(): StreamInterface
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $resource = fopen($this->file, 'r');

        return Stream::new($resource);
    }

    // Move

    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if (!is_string($targetPath) || '' === $targetPath) {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        if (null !== $this->file) {
            $this->moved = 'cli' === PHP_SAPI ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
        } else {
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $this->copyStreamContent($stream, $targetPath);
            $this->moved = true;
        }

        if (false === $this->moved) {
            throw new RuntimeException(sprintf('Uploaded file could not be moved to %s', $targetPath));
        }
    }

    // Validate

    private function validateActive(): void
    {
        if (UPLOAD_ERR_OK !== $this->error) {
            throw new UploadErrorException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new StreamAlreadyMovedException('Cannot retrieve stream after it has already been moved');
        }
    }

    // Copy

    private function copyStreamContent(StreamInterface $stream, string $targetPath): void
    {
        $dest = Stream::new(fopen($targetPath, 'w'));
        while (!$stream->eof()) {
            if (!$dest->write($stream->read(1048576))) {
                break;
            }
        }
    }
}