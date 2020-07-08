<?php

declare(strict_types=1);

namespace Tests\Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\InvalidUploadErrorException;
use Arslanoov\Psr7\Exception\RuntimeException;
use Arslanoov\Psr7\Exception\UploadErrorException;
use Arslanoov\Psr7\Stream;
use Arslanoov\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class UploadedFileTest extends TestCase
{
    protected array $cleanup;

    public function setUp(): void
    {
        $this->cleanup = [];
    }

    public function tearDown(): void
    {
        foreach ($this->cleanup as $file) {
            if (is_scalar($file) and file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testGetError(): void
    {
        $stream = Stream::new('');
        $upload = new UploadedFile($stream, 0, $error = UPLOAD_ERR_OK);

        $this->assertSame($error, $upload->getError());
    }

    public function testReturnOriginalStream(): void
    {
        $stream = Stream::new('');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->assertSame($stream, $upload->getStream());
    }

    public function testReturnWrappedPhpStream(): void
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();

        $this->assertSame($stream, $uploadStream);
    }

    public function testGetStream(): void
    {
        $upload = new UploadedFile(__DIR__.'/Resources/foo.txt', 0, UPLOAD_ERR_OK);
        $stream = $upload->getStream();
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertEquals("Foobar", $stream->__toString());
    }

    public function testInvalidStatus(): void
    {
        $this->expectException(InvalidUploadErrorException::class);
        $this->expectExceptionMessage('Invalid error status for UploadedFile');
        new UploadedFile(__DIR__.'/Resources/foo.txt', 0, 100);
    }

    public function testInvalidStreamOrFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream or file provided for UploadedFile');
        new UploadedFile(true, 0, UPLOAD_ERR_OK);
    }

    public function testSuccess(): void
    {
        $stream = Stream::new('Foo bar!');
        $upload = new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK, 'filename.txt', 'text/plain');

        $this->assertEquals($stream->getSize(), $upload->getSize());
        $this->assertEquals('filename.txt', $upload->getClientFilename());
        $this->assertEquals('text/plain', $upload->getClientMediaType());

        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'successful');
        $upload->moveTo($to);
        $this->assertFileExists($to);
        $this->assertEquals($stream->__toString(), file_get_contents($to));
    }

    public function testCallMoreThanOnce(): void
    {
        $stream = Stream::new('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');
        $upload->moveTo($to);
    }

    public function testRetrieveStreamAfterMove(): void
    {
        $stream = Stream::new('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertFileExists($to);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');
        $upload->getStream();
    }

    public function testMoveToCreatesStream(): void
    {
        $this->cleanup[] = $from = tempnam(sys_get_temp_dir(), 'copy_from');
        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'copy_to');

        copy(__FILE__, $from);

        $uploadedFile = new UploadedFile($from, 100, UPLOAD_ERR_OK, basename($from), 'text/plain');
        $uploadedFile->moveTo($to);

        $this->assertFileEquals(__FILE__, $to);
    }

    // Use provider

    public function invalidMovePaths(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'empty' => [''],
            'array' => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    public function invalidFilenamesAndMediaTypes(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'array' => [['string']],
            'object' => [(object) ['string']],
        ];
    }

    public function invalidStreams(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'array' => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    public function nonOkErrorStatus(): array
    {
        return [
            'UPLOAD_ERR_INI_SIZE' => [UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE' => [UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL' => [UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE' => [UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION' => [UPLOAD_ERR_EXTENSION],
        ];
    }

    /**
     * @dataProvider invalidMovePaths
     */
    public function testInvalidPath($path): void
    {
        $stream = Stream::new('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('path');
        $upload->moveTo($path);
    }

    /**
     * @dataProvider nonOkErrorStatus
     */
    public function testMoveToRaisesException($status): void
    {
        $this->expectException(RuntimeException::class);
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        $uploadedFile->moveTo(__DIR__ . '/' . uniqid());
    }

    /**
     * @dataProvider nonOkErrorStatus
     */
    public function testGetStreamRaisesException($status): void
    {
        $this->expectException(UploadErrorException::class);
        $uploadedFile = new UploadedFile('not ok', 0, $status);
    }
}