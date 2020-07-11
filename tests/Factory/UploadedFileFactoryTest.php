<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Factory;

use Furious\Psr7\Factory\UploadedFileFactory;
use Furious\Psr7\Stream;
use Furious\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $stream = Stream::new('Foo bar!');
        $uploadedFile = (new UploadedFileFactory())->createUploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK, 'filename.txt', 'text/plain');

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
    }
}