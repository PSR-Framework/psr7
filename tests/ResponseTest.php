<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Response;
use Arslanoov\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $firstResponse = new Response();
        $this->assertSame(200, $firstResponse->getStatusCode());
        $this->assertSame('1.1', $firstResponse->getProtocolVersion());
        $this->assertSame('OK', $firstResponse->getReasonPhrase());
        $this->assertSame([], $firstResponse->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $firstResponse->getBody());
        $this->assertSame('', (string) $firstResponse->getBody());
    }

    public function testConstructWithStatusCode(): void
    {
        $firstResponse = new Response(404);
        $this->assertSame(404, $firstResponse->getStatusCode());
        $this->assertSame('Not Found', $firstResponse->getReasonPhrase());
    }

    public function testConstructUndefinedStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status code has to be an integer between 100 and 599');
        $firstResponse = new Response(999);
    }

    public function testConstructEmptyReason(): void
    {
        $firstResponse = new Response(404, [], null, '1.1', '');
        $this->assertSame(404, $firstResponse->getStatusCode());
        $this->assertSame('', $firstResponse->getReasonPhrase());
    }

    public function testConstructorNotReadStreamBody(): void
    {
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();
        $body->expects($this->never())
            ->method('__toString');

        $firstResponse = new Response(200, [], $body);
        $this->assertSame($body, $firstResponse->getBody());
    }

    public function testConstructWithHeaders(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame('Bar', $firstResponse->getHeaderLine('Foo'));
        $this->assertSame(['Bar'], $firstResponse->getHeader('Foo'));
    }

    public function testConstructWithArrayHeaders(): void
    {
        $firstResponse = new Response(200, [
            'Foo' => ['baz', 'bar'],
        ]);
        $this->assertSame(['Foo' => ['baz', 'bar']], $firstResponse->getHeaders());
        $this->assertSame('baz, bar', $firstResponse->getHeaderLine('Foo'));
        $this->assertSame(['baz', 'bar'], $firstResponse->getHeader('Foo'));
    }

    public function testConstructWithBody(): void
    {
        $firstResponse = new Response(200, [], 'baz');
        $this->assertInstanceOf(StreamInterface::class, $firstResponse->getBody());
        $this->assertSame('baz', (string) $firstResponse->getBody());
    }

    public function testNullBody(): void
    {
        $firstResponse = new Response(200, [], null);
        $this->assertInstanceOf(StreamInterface::class, $firstResponse->getBody());
        $this->assertSame('', (string) $firstResponse->getBody());
    }

    public function testZeroBody(): void
    {
        $firstResponse = new Response(200, [], '0');
        $this->assertInstanceOf(StreamInterface::class, $firstResponse->getBody());
        $this->assertSame('0', (string) $firstResponse->getBody());
    }

    public function testConstructWithReason(): void
    {
        $firstResponse = new Response(200, [], null, '1.1', 'bar');
        $this->assertSame('bar', $firstResponse->getReasonPhrase());

        $firstResponse = new Response(200, [], null, '1.1', '0');
        $this->assertSame('0', $firstResponse->getReasonPhrase(), 'Falsey reason works');
    }

    public function testConstructWithProtocolVersion(): void
    {
        $firstResponse = new Response(200, [], null, '1000');
        $this->assertSame('1000', $firstResponse->getProtocolVersion());
    }

    public function testNoReason(): void
    {
        $firstResponse = (new Response())->withStatus(201);
        $this->assertSame(201, $firstResponse->getStatusCode());
        $this->assertSame('Created', $firstResponse->getReasonPhrase());
    }

    public function testWithStatusCodeAndReason(): void
    {
        $firstResponse = (new Response())->withStatus(201, 'Foo');
        $this->assertSame(201, $firstResponse->getStatusCode());
        $this->assertSame('Foo', $firstResponse->getReasonPhrase());

        $firstResponse = (new Response())->withStatus(201, '0');
        $this->assertSame(201, $firstResponse->getStatusCode());
        $this->assertSame('0', $firstResponse->getReasonPhrase(), 'Falsey reason works');
    }

    public function testWithProtocolVersion(): void
    {
        $firstResponse = (new Response())->withProtocolVersion('1000');
        $this->assertSame('1000', $firstResponse->getProtocolVersion());
    }

    public function testWithBody(): void
    {
        $b = Stream::new('0');
        $firstResponse = (new Response())->withBody($b);
        $this->assertInstanceOf(StreamInterface::class, $firstResponse->getBody());
        $this->assertSame('0', (string) $firstResponse->getBody());
    }

    public function testWithHeader(): void
    {
        $firstResponse = new Response(200, [
            'Foo' => 'Bar'
        ]);
        $secondResponse = $firstResponse->withHeader('baZ', 'Bam');

        $this->assertSame($firstResponse->getHeaders(), [
            'Foo' => ['Bar']
        ]);
        $this->assertSame($secondResponse->getHeaders(),
            [
                'Foo' => ['Bar'],
                'baZ' => ['Bam']
            ]
        );
        $this->assertSame('Bam', $secondResponse->getHeaderLine('baz'));
        $this->assertSame(['Bam'], $secondResponse->getHeader('baz'));
    }

    public function testWithArrayHeader(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $secondResponse = $firstResponse->withHeader('baZ', ['Bam', 'Bar']);
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam', 'Bar']], $secondResponse->getHeaders());
        $this->assertSame('Bam, Bar', $secondResponse->getHeaderLine('baz'));
        $this->assertSame(['Bam', 'Bar'], $secondResponse->getHeader('baz'));
    }

    public function testReplaceDifferentCase(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $secondResponse = $firstResponse->withHeader('foO', 'Bam');
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame(['foO' => ['Bam']], $secondResponse->getHeaders());
        $this->assertSame('Bam', $secondResponse->getHeaderLine('foo'));
        $this->assertSame(['Bam'], $secondResponse->getHeader('foo'));
    }

    public function testAddedHeader(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $secondResponse = $firstResponse->withAddedHeader('foO', 'Baz');
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame(['Foo' => ['Bar', 'Baz']], $secondResponse->getHeaders());
        $this->assertSame('Bar, Baz', $secondResponse->getHeaderLine('foo'));
        $this->assertSame(['Bar', 'Baz'], $secondResponse->getHeader('foo'));
    }

    public function testAddedHeaderAsArray(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $secondResponse = $firstResponse->withAddedHeader('foO', ['Baz', 'Bam']);
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame(['Foo' => ['Bar', 'Baz', 'Bam']], $secondResponse->getHeaders());
        $this->assertSame('Bar, Baz, Bam', $secondResponse->getHeaderLine('foo'));
        $this->assertSame(['Bar', 'Baz', 'Bam'], $secondResponse->getHeader('foo'));
    }

    public function testAddedHeaderNotExists(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar']);
        $secondResponse = $firstResponse->withAddedHeader('nEw', 'Baz');
        $this->assertSame(['Foo' => ['Bar']], $firstResponse->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'nEw' => ['Baz']], $secondResponse->getHeaders());
        $this->assertSame('Baz', $secondResponse->getHeaderLine('new'));
        $this->assertSame(['Baz'], $secondResponse->getHeader('new'));
    }

    public function testWithoutHeaderExists(): void
    {
        $firstResponse = new Response(200, ['Foo' => 'Bar', 'Baz' => 'Bam']);
        $secondResponse = $firstResponse->withoutHeader('foO');
        $this->assertTrue($firstResponse->hasHeader('foo'));
        $this->assertSame(['Foo' => ['Bar'], 'Baz' => ['Bam']], $firstResponse->getHeaders());
        $this->assertFalse($secondResponse->hasHeader('foo'));
        $this->assertSame(['Baz' => ['Bam']], $secondResponse->getHeaders());
    }

    public function testWithoutHeaderThatDoesNotExist(): void
    {
        $firstResponse = new Response(200, ['Baz' => 'Bam']);
        $secondResponse = $firstResponse->withoutHeader('foO');
        $this->assertSame($firstResponse, $secondResponse);
        $this->assertFalse($secondResponse->hasHeader('foo'));
        $this->assertSame(['Baz' => ['Bam']], $secondResponse->getHeaders());
    }

    public function testSameInstance(): void
    {
        $response = new Response();
        $this->assertSame($response, $response->withoutHeader('foo'));
    }

    // Use Provider

    public function trimmedHeaderValues(): array
    {
        return [
            [new Response(200, ['OWS' => " \t \tFoo\t \t "])],
            [(new Response())->withHeader('OWS', " \t \tFoo\t \t ")],
            [(new Response())->withAddedHeader('OWS', " \t \tFoo\t \t ")],
        ];
    }

    /**
     * @dataProvider trimmedHeaderValues
     * @param Response $response
     */
    public function testHeaderValuesAreTrimmed($response): void
    {
        $this->assertSame(['OWS' => ['Foo']], $response->getHeaders());
        $this->assertSame('Foo', $response->getHeaderLine('OWS'));
        $this->assertSame(['Foo'], $response->getHeader('OWS'));
    }
}