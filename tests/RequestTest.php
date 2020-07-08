<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\UnableToParseUriException;
use Arslanoov\Psr7\Request;
use Arslanoov\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class RequestTest extends TestCase
{
    // Uri

    public function testStringUri(): void
    {
        $request = new Request('GET', '/home');
        $this->assertEquals('/home', (string) $request->getUri());
    }

    public function testObjectUri(): void
    {
        $uri = new Uri('/');
        $request = new Request('GET', $uri);
        $this->assertSame($uri, $request->getUri());
    }

    public function testIncorrectUri(): void
    {
        $this->expectException(UnableToParseUriException::class);
        $this->expectExceptionMessage('Unable to parse URI: ////');

        new Request('GET', '////');
    }

    // Body

    public function testBodyFromConstructor(): void
    {
        $request = new Request('GET', '/', [], 'body');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('body', (string) $request->getBody());
    }

    public function testEmptyBody(): void
    {
        $request = new Request('GET', '/', [], null);
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testZeroBody(): void
    {
        $request = new Request('GET', '/', [], '0');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('0', (string) $request->getBody());
    }

    public function testConstructorDoesNotReadStreamBody(): void
    {
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();
        $body->expects($this->never())->method('__toString');

        $request = new Request('GET', '/', [], $body);
        $this->assertSame($body, $request->getBody());
    }

    // With

    public function testWithRequestTarget(): void
    {
        $firstRequest = new Request('GET', '/');
        $secondRequest = $firstRequest
            ->withRequestTarget('*');

        $this->assertEquals('*', $secondRequest->getRequestTarget());
        $this->assertEquals('/', $firstRequest->getRequestTarget());
    }

    public function testInvalidRequestTarget(): void
    {
        $request = new Request('GET', '/');
        $this->expectException(InvalidArgumentException::class);
        $request->withRequestTarget('foo bar');
    }

    public function testGetRequestTarget(): void
    {
        $request = new Request('GET', 'https://example.com');
        $this->assertEquals('/', $request->getRequestTarget());

        $request = new Request('GET', 'https://example.com/foo?bar=baz');
        $this->assertEquals('/foo?bar=baz', $request->getRequestTarget());

        $request = new Request('GET', 'https://example.com?bar=foo');
        $this->assertEquals('/?bar=foo', $request->getRequestTarget());
    }

    public function testRequestTargetWithSpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $request = new Request('GET', '/');
        $request->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultSlash(): void
    {
        $request = new Request('GET', '');
        $this->assertEquals('/', $request->getRequestTarget());

        $request = new Request('GET', '*');
        $this->assertEquals('*', $request->getRequestTarget());

        $request = new Request('GET', 'http://example.com/foo bar/');
        $this->assertEquals('/foo%20bar/', $request->getRequestTarget());
    }

    public function testRequestTarget(): void
    {
        $request = new Request('GET', 'http://foo.com/baz?bar=bam');
        $this->assertEquals('/baz?bar=bam', $request->getRequestTarget());
    }

    public function testRequestTargetWithZeroQuery(): void
    {
        $request = new Request('GET', 'http://foo.com/bar?0');
        $this->assertEquals('/bar?0', $request->getRequestTarget());
    }

    public function testHostAdded(): void
    {
        $request = new Request('GET', 'http://example.com/baz?bar=bam', ['Foo' => 'Bar']);
        $this->assertEquals($request->getHeaders(), [
            'Host' => ['example.com'],
            'Foo' => ['Bar']
        ]);
    }

    public function testGetHeaderLine(): void
    {
        $request = new Request('GET', 'http://foo.com/baz?bar=bam', [
            'Foo' => ['word', 'word2', 'word3'],
        ]);

        $this->assertEquals('word, word2, word3', $request->getHeaderLine('Foo'));
        $this->assertEquals('', $request->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwritten(): void
    {
        $firstRequest = new Request('GET', 'http://foo.com/baz?bar=bam', ['Host' => 'example.com']);
        $this->assertEquals($firstRequest->getHeaders(), [
            'Host' => ['example.com']
        ]);

        $secondRequest = $firstRequest->withUri(new Uri('http://www.foo.com/bar'), true);
        $this->assertEquals('example.com', $secondRequest->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri(): void
    {
        $firstRequest = new Request('GET', 'http://foo.com/baz?bar=bam');
        $this->assertEquals($firstRequest->getHeaders(), [
            'Host' => ['foo.com']
        ]);

        $secondRequest = $firstRequest->withUri(new Uri('http://www.baz.com/bar'));
        $this->assertEquals('www.baz.com', $secondRequest->getHeaderLine('Host'));
    }

    public function testMergeHeaders(): void
    {
        $request = new Request('GET', '', [
            'BAR' => 'foo',
            'bar' => ['foo', 'foo'],
        ]);

        $this->assertEquals($request->getHeaders(), [
            'BAR' => [
                'foo', 'foo', 'foo'
            ]
        ]);

        $this->assertEquals('foo, foo, foo', $request->getHeaderLine('bar'));
    }

    public function testNumericHeaders(): void
    {
        $request = new Request('GET', '', [
            'Content-Length' => 1000
        ]);

        $this->assertSame($request->getHeaders(), [
            'Content-Length' => ['1000']
        ]);
        $this->assertSame('1000', $request->getHeaderLine('Content-Length'));
    }

    public function testNumericHeaderNames(): void
    {
        $request = new Request(
            'GET', '', [
                '200' => 'NumericHeaderValue',
                '0'   => 'NumericHeaderValueZero',
            ]
        );

        $this->assertSame($request->getHeaders(), [
                '200' => ['NumericHeaderValue'],
                '0'   => ['NumericHeaderValueZero'],
            ]
        );

        $this->assertSame(['NumericHeaderValue'], $request->getHeader('200'));
        $this->assertSame('NumericHeaderValue', $request->getHeaderLine('200'));

        $this->assertSame(['NumericHeaderValueZero'], $request->getHeader('0'));
        $this->assertSame('NumericHeaderValueZero', $request->getHeaderLine('0'));

        $request = $request
            ->withHeader('300', 'NumericHeaderValue2')
            ->withAddedHeader('200', ['A', 'B'])
        ;

        $this->assertSame($request->getHeaders(), [
                '200' => ['NumericHeaderValue', 'A', 'B'],
                '0'   => ['NumericHeaderValueZero'],
                '300' => ['NumericHeaderValue2'],
            ]
        );

        $request = $request->withoutHeader('300');
        $this->assertSame($request->getHeaders(), [
                '200' => ['NumericHeaderValue', 'A', 'B'],
                '0'   => ['NumericHeaderValueZero'],
            ]
        );
    }

    public function testAddPort(): void
    {
        $request = new Request('GET', 'http://example.com:9000/bar');
        $this->assertEquals('example.com:9000', $request->getHeaderLine('host'));
    }

    public function testReplacePreviousPort(): void
    {
        $request = new Request('GET', 'http://example.com:9000/bar');
        $request = $request->withUri(new Uri('http://example.com:9000/bar'));
        $this->assertEquals('example.com:9000', $request->getHeaderLine('host'));
    }

    public function testHeaderWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name must be an RFC 7230 compatible string.');
        $request = new Request('GET', 'https://example.com/');
        $request->withHeader('', 'Foo');
    }

    public function testHeaderWithEmptyValue(): void
    {
        $request = new Request('GET', 'https://example.com/');
        $request = $request->withHeader('Foo', '');
        $this->assertEquals([''], $request->getHeader('Foo'));
    }

    public function testUpdateHostFromUri(): void
    {
        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://example.com'));
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));

        $request = new Request('GET', 'https://example.com/');
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));
        $request = $request->withUri(new Uri('https://somesite.com'));
        $this->assertEquals('somesite.com', $request->getHeaderLine('Host'));

        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://somesite.com:7000'));
        $this->assertEquals('somesite.com:7000', $request->getHeaderLine('Host'));

        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://somesite.com:443'));
        $this->assertEquals('somesite.com', $request->getHeaderLine('Host'));
    }
}