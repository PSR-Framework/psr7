<?php

declare(strict_types=1);

namespace Tests\Furious\Psr7;

use Furious\Psr7\Exception\InvalidArgumentException;
use Furious\Psr7\ServerRequest;
use Furious\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ServerRequestTest extends TestCase
{
    public function testConstructorStringParsedBody(): void
    {
        $body = '{"foo": "bar"}';
        $request = new ServerRequest('GET', '/', '1.1', [], [], $body);

        $this->assertInstanceOf(StreamInterface::class, $request->getParsedBody());
    }

    public function testConstructorNullParsedBody(): void
    {
        $request = new ServerRequest('GET', '/', '1.1', [], [], null);

        $this->assertNull($request->getParsedBody());
    }

    public function testConstructorErrorParsedBody(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Body must be array, instance of StreamInterface or null');

        new ServerRequest('GET', '/', '1.1', [], [], true);
    }

    public function testWithArrayParsedBody(): void
    {
        $request = new ServerRequest('GET', '/', '1.1');
        $request = $request
            ->withParsedBody($body = ['foo' => 'bar'])
        ;

        $this->assertEquals($request->getParsedBody(), $body);
    }

    public function testWithStringParsedBody(): void
    {
        $request = new ServerRequest('GET', '/', '1.1');
        $request = $request
            ->withParsedBody($body = '{"foo": "bar"}')
        ;

        $this->assertInstanceOf(StreamInterface::class, $request->getParsedBody());
    }

    public function testWithNullParsedBody(): void
    {
        $request = new ServerRequest('GET', '/', '1.1');
        $request = $request
            ->withParsedBody(null)
        ;

        $this->assertNull($request->getParsedBody());
    }

    public function testWithErrorParsedBody(): void
    {
        $request = new ServerRequest('GET', '/', '1.1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Body must be array, instance of StreamInterface or null');

        $request
            ->withParsedBody(true)
        ;
    }

    // Server

    public function testServerParams(): void
    {
        $params = ['foo' => 'bar'];

        $request = new ServerRequest('GET', '/', '1.1', [], [], null, $params);
        $this->assertSame($params, $request->getServerParams());
    }

    public function testCookieParams(): void
    {
        $firstRequest = new ServerRequest('GET', '/');

        $params = ['foo' => 'bar'];

        $secondRequest = $firstRequest->withCookieParams($params);

        $this->assertNotSame($secondRequest, $firstRequest);
        $this->assertEmpty($firstRequest->getCookieParams());
        $this->assertSame($params, $secondRequest->getCookieParams());
    }

    public function testUploadedFiles(): void
    {
        $firstRequest = new ServerRequest('GET', '/');

        $files = [
            'file' => new UploadedFile('test', 1111, UPLOAD_ERR_OK),
        ];

        $secondRequest = $firstRequest->withUploadedFiles($files);

        $this->assertNotSame($secondRequest, $firstRequest);
        $this->assertSame([], $firstRequest->getUploadedFiles());
        $this->assertSame($files, $secondRequest->getUploadedFiles());
    }

    public function testParsedBody(): void
    {
        $firstRequest = new ServerRequest('GET', '/');

        $params = ['name' => 'value'];

        $secondRequest = $firstRequest->withParsedBody($params);

        $this->assertNotSame($secondRequest, $firstRequest);
        $this->assertEmpty($firstRequest->getParsedBody());
        $this->assertSame($params, $secondRequest->getParsedBody());
    }

    public function testAttributes(): void
    {
        $firstRequest = new ServerRequest('GET', '/');

        $secondRequest = $firstRequest->withAttribute('name', 'value');
        $thirdRequest = $secondRequest->withAttribute('other', 'otherValue');
        $fourthRequest = $thirdRequest->withoutAttribute('other');
        $request5 = $thirdRequest->withoutAttribute('unknown');

        $this->assertNotSame($secondRequest, $firstRequest);
        $this->assertNotSame($thirdRequest, $secondRequest);
        $this->assertNotSame($fourthRequest, $thirdRequest);
        $this->assertNotSame($request5, $fourthRequest);

        $this->assertEmpty($firstRequest->getAttributes());
        $this->assertEmpty($firstRequest->getAttribute('name'));
        $this->assertEquals(
            'something',
            $firstRequest->getAttribute('name', 'something'),
            'Should return the default value'
        );

        $this->assertEquals('value', $secondRequest->getAttribute('name'));
        $this->assertEquals(['name' => 'value'], $secondRequest->getAttributes());
        $this->assertEquals(['name' => 'value', 'other' => 'otherValue'], $thirdRequest->getAttributes());
        $this->assertEquals(['name' => 'value'], $fourthRequest->getAttributes());
    }

    public function testNullAttribute(): void
    {
        $request = (new ServerRequest('GET', '/'))->withAttribute('name', null);

        $this->assertSame(['name' => null], $request->getAttributes());
        $this->assertNull($request->getAttribute('name', 'different-default'));

        $requestWithoutAttribute = $request->withoutAttribute('name');

        $this->assertSame([], $requestWithoutAttribute->getAttributes());
        $this->assertSame('different-default', $requestWithoutAttribute->getAttribute('name', 'different-default'));
    }
}