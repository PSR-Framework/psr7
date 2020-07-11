<?php

declare(strict_types=1);

namespace Tests\Furious\Psr7;

use Furious\Psr7\Exception\InvalidArgumentException;
use Furious\Psr7\Exception\InvalidPortException;
use Furious\Psr7\Exception\UnableToParseUriException;
use Furious\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private const RFC3986_BASE = 'http://a/b/c/d;p?q';

    public function testParse(): void
    {
        $uri = new Uri('https://someuser:pass@test.com:2222/path/xxx?q=abc#test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('someuser:pass@test.com:2222', $uri->getAuthority());
        $this->assertSame('someuser:pass', $uri->getUserInfo());
        $this->assertSame('test.com', $uri->getHost());
        $this->assertSame(2222, $uri->getPort());
        $this->assertSame('/path/xxx', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://someuser:pass@test.com:2222/path/xxx?q=abc#test', (string) $uri);
    }

    public function testTransformAndRetrieve(): void
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('test.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test')
        ;

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@test.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('test.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@test.com:8080/path/123?q=abc#test', (string) $uri);
    }

    public function testValidPort(): void
    {
        $this->expectException(InvalidPortException::class);
        $this->expectExceptionMessage('Invalid port: 77777. Must be between 0 and 65535');

        (new Uri())->withPort(77777);
    }

    public function testNegativePort(): void
    {
        $this->expectException(InvalidPortException::class);
        $this->expectExceptionMessage('Invalid port: -10. Must be between 0 and 65535');

        (new Uri())->withPort(-10);
    }

    public function testZeroPort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse URI');

        new Uri('//somesite.com:0');
    }

    // Correct type

    public function testSchemeCorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scheme must be a string');

        (new Uri())->withScheme([]);
    }

    public function testHostCorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Host must be a string');

        (new Uri())->withHost([]);
    }

    public function testPathCorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path must be a string');

        (new Uri())->withPath([]);
    }

    public function testQueryCorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must be a string');

        (new Uri())->withQuery([]);
    }

    public function testFragmentCorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fragment must be a string');

        (new Uri())->withFragment([]);
    }

    public function testParseFalseUriParts(): void
    {
        $uri = new Uri('0://0:0@0/0?0#0');

        $this->assertSame('0', $uri->getScheme());
        $this->assertSame('0:0@0', $uri->getAuthority());
        $this->assertSame('0:0', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('0://0:0@0/0?0#0', (string) $uri);
    }

    public function testParseFalseUriPartsByConstructor(): void
    {
        $uri = (new Uri())
            ->withScheme('0')
            ->withUserInfo('0', '0')
            ->withHost('0')
            ->withPath('/0')
            ->withQuery('0')
            ->withFragment('0');

        $this->assertSame('0', $uri->getScheme());
        $this->assertSame('0:0@0', $uri->getAuthority());
        $this->assertSame('0:0', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('0://0:0@0/0?0#0', (string) $uri);
    }

    // Lowercase

    public function testSchemeLowercase(): void
    {
        $uri = new Uri('HTTP://somesite.com');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://somesite.com', (string) $uri);

        $uri = (new Uri('//somesite.com'))->withScheme('HTTP');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://somesite.com', (string) $uri);
    }

    public function testHostLowercase(): void
    {
        $uri = new Uri('//somesite.com');

        $this->assertSame('somesite.com', $uri->getHost());
        $this->assertSame('//somesite.com', (string) $uri);

        $uri = (new Uri())->withHost('somesite.com');

        $this->assertSame('somesite.com', $uri->getHost());
        $this->assertSame('//somesite.com', (string) $uri);
    }

    public function testStandardPort(): void
    {
        $uri = new Uri('https://somesite.com:443');
        $this->assertNull($uri->getPort());
        $this->assertSame('somesite.com', $uri->getAuthority());

        $uri = (new Uri('https://somesite.com'))->withPort(443);
        $this->assertNull($uri->getPort());
        $this->assertSame('somesite.com', $uri->getAuthority());

        $uri = new Uri('http://somesite.com:80');
        $this->assertNull($uri->getPort());
        $this->assertSame('somesite.com', $uri->getAuthority());

        $uri = (new Uri('http://somesite.com'))->withPort(80);
        $this->assertNull($uri->getPort());
        $this->assertSame('somesite.com', $uri->getAuthority());
    }

    public function testPortReturnIfSchemeUnknown(): void
    {
        $uri = (new Uri('//somesite.com'))->withPort(80);

        $this->assertSame(80, $uri->getPort());
        $this->assertSame('somesite.com:80', $uri->getAuthority());
    }

    public function testStandardPortIsNullIfSchemeChanges(): void
    {
        $uri = new Uri('http://somesite.com:443');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(443, $uri->getPort());

        $uri = $uri->withScheme('https');
        $this->assertNull($uri->getPort());
    }

    public function testPortPassedAsStringIsCastedToInt(): void
    {
        $uri = (new Uri('//somesite.com'))->withPort('8080');

        $this->assertSame(8080, $uri->getPort(), 'Port is returned as integer');
        $this->assertSame('somesite.com:8080', $uri->getAuthority());
    }

    public function testRemovePort(): void
    {
        $uri = (new Uri('http://somesite.com:8080'))->withPort(null);

        $this->assertNull($uri->getPort());
        $this->assertSame('http://somesite.com', (string) $uri);
    }

    public function testAuthorityWithUserInfoWithoutHost(): void
    {
        $uri = (new Uri())->withUserInfo('user', 'password');

        $this->assertSame('user:password', $uri->getUserInfo());
        $this->assertSame('', $uri->getAuthority());
    }

    // Encode

    public function testWithPathEncode(): void
    {
        $uri = (new Uri())->withPath('/baz?#€/b%61r');
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', $uri->getPath());
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', (string) $uri);
    }

    public function testWithQueryEncode(): void
    {
        $uri = (new Uri())->withQuery('?=#&€=/&b%61r');
        $this->assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        $this->assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    public function testWithFragmentEncode(): void
    {
        $uri = (new Uri())->withFragment('#€?/b%61r');
        $this->assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        $this->assertSame('#%23%E2%82%AC?/b%61r', (string) $uri);
    }

    public function testRelativeUri(): void
    {
        $uri = (new Uri())->withPath('foo');
        $this->assertSame('foo', $uri->getPath());
        $this->assertSame('foo', (string) $uri);
    }

    public function testAddsSlashForRelativeUri(): void
    {
        $uri = (new Uri())->withPath('foo')->withHost('somesite.com');
        $this->assertSame('foo', $uri->getPath());
        $this->assertSame('//somesite.com/foo', (string) $uri);
    }

    public function testRemoveExtraSlashes(): void
    {
        $uri = (new Uri())->withPath('////foo');
        $this->assertSame('////foo', $uri->getPath());
        $this->assertSame('/foo', (string) $uri);
    }

    public function testDefaultReturnGetters(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
    }

    public function testImmutability(): void
    {
        $uri = new Uri();

        $newUri = $uri->withScheme('https');

        $this->assertNotSame($uri, $uri->withScheme('https'));
        $this->assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        $this->assertNotSame($uri, $uri->withHost('somesite.com'));
        $this->assertNotSame($uri, $uri->withPort(8080));
        $this->assertNotSame($uri, $uri->withPath('/path/123'));
        $this->assertNotSame($uri, $uri->withQuery('q=abc'));
        $this->assertNotSame($uri, $uri->withFragment('test'));
        $this->assertNotSame($uri, $newUri);
    }

    public function testUtf8Host(): void
    {
        $uri = new Uri('http://сайт.рф/');
        $this->assertSame('сайт.рф', $uri->getHost());

        $new = $uri->withHost('程式设计.com');
        $this->assertSame('程式设计.com', $new->getHost());

        $testDomain = 'παράδειγμα.δοκιμή';
        $uri = (new Uri())->withHost($testDomain);
        $this->assertSame($testDomain, $uri->getHost());
        $this->assertSame('//' . $testDomain, (string) $uri);
    }

    // Use provider

    public function testCanTransformAndRetrievePartsIndividually(): void
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    /**
     * @dataProvider getValidUris
     * @param string $input
     */
    public function testValidUrisStayValid(string $input): void
    {
        $uri = new Uri($input);

        $this->assertSame($input, (string) $uri);
    }

    public function getValidUris(): array
    {
        return [
            ['urn:path-rootless'],
            ['urn:path:with:colon'],
            ['urn:/path-absolute'],
            ['urn:/'],
            ['urn:'],
            ['/'],
            ['relative/'],
            ['0'],
            [''],
            ['//example.com'],
            ['//example.com/'],
            ['//example.com?q#h'],
            ['?q'],
            ['?q=abc&foo=bar'],
            ['#fragment'],
            ['./foo/../bar'],
        ];
    }

    /**
     * @dataProvider getInvalidUris
     * @param $invalidUri string
     */
    public function testInvalidUrisThrowException(string $invalidUri): void
    {
        $this->expectException(UnableToParseUriException::class);
        $this->expectExceptionMessage('Unable to parse URI');

        new Uri($invalidUri);
    }

    public function getInvalidUris(): array
    {
        return [
            ['https://'],
            ['urn://host:with:colon']
        ];
    }
}