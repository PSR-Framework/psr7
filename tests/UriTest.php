<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\InvalidPortException;
use Arslanoov\Psr7\Exception\UnableToParseUriException;
use Arslanoov\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private const RFC3986_BASE = 'http://a/b/c/d;p?q';

    public function testParsesProvidedUri()
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

    public function testCanTransformAndRetrieve()
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('test.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');

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

    public function testPortMustBeValid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port: 100000. Must be between 0 and 65535');

        (new Uri())->withPort(100000);
    }

    public function testWithPortCannotBeNegative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port: -1. Must be between 0 and 65535');

        (new Uri())->withPort(-1);
    }

    public function testParseUriPortZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse URI');

        new Uri('//example.com:0');
    }

    public function testSchemeMustHaveCorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scheme must be a string');

        (new Uri())->withScheme([]);
    }

    public function testHostCorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Host must be a string');

        (new Uri())->withHost([]);
    }

    public function testPathCorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path must be a string');

        (new Uri())->withPath([]);
    }

    public function testQueryMustHaveCorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must be a string');

        (new Uri())->withQuery([]);
    }

    public function testFragmentMustHaveCorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fragment must be a string');

        (new Uri())->withFragment([]);
    }

    public function testCanParseFalseUriParts()
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

    public function testCanConstructFalseUriParts()
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

    public function testSchemeIsNormalizedToLowercase()
    {
        $uri = new Uri('HTTP://example.com');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string) $uri);

        $uri = (new Uri('//example.com'))->withScheme('HTTP');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string) $uri);
    }

    public function testHostIsNormalizedToLowercase()
    {
        $uri = new Uri('//eXaMpLe.CoM');

        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);

        $uri = (new Uri())->withHost('eXaMpLe.CoM');

        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);
    }

    public function testPortIsNullIfStandardPortForScheme()
    {
        // HTTPS standard port
        $uri = new Uri('https://example.com:443');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('https://example.com'))->withPort(443);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        // HTTP standard port
        $uri = new Uri('http://example.com:80');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('http://example.com'))->withPort(80);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());
    }

    public function testPortIsReturnedIfSchemeUnknown()
    {
        $uri = (new Uri('//example.com'))->withPort(80);

        $this->assertSame(80, $uri->getPort());
        $this->assertSame('example.com:80', $uri->getAuthority());
    }

    public function testStandardPortIsNullIfSchemeChanges()
    {
        $uri = new Uri('http://example.com:443');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(443, $uri->getPort());

        $uri = $uri->withScheme('https');
        $this->assertNull($uri->getPort());
    }

    public function testPortPassedAsStringIsCastedToInt()
    {
        $uri = (new Uri('//example.com'))->withPort('8080');

        $this->assertSame(8080, $uri->getPort(), 'Port is returned as integer');
        $this->assertSame('example.com:8080', $uri->getAuthority());
    }

    public function testPortCanBeRemoved()
    {
        $uri = (new Uri('http://example.com:8080'))->withPort(null);

        $this->assertNull($uri->getPort());
        $this->assertSame('http://example.com', (string) $uri);
    }

    public function testAuthorityWithUserInfoButWithoutHost()
    {
        $uri = (new Uri())->withUserInfo('user', 'pass');

        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('', $uri->getAuthority());
    }

    public function uriComponentsEncodingProvider()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            // Percent encode spaces
            ['/pa th?q=va lue#frag ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode multibyte
            ['/€?€#€', '/%E2%82%AC', '%E2%82%AC', '%E2%82%AC', '/%E2%82%AC?%E2%82%AC#%E2%82%AC'],
            // Don't encode something that's already encoded
            ['/pa%20th?q=va%20lue#frag%20ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode invalid percent encodings
            ['/pa%2-th?q=va%2-lue#frag%2-ment', '/pa%252-th', 'q=va%252-lue', 'frag%252-ment', '/pa%252-th?q=va%252-lue#frag%252-ment'],
            // Don't encode path segments
            ['/pa/th//two?q=va/lue#frag/ment', '/pa/th//two', 'q=va/lue', 'frag/ment', '/pa/th//two?q=va/lue#frag/ment'],
            // Don't encode unreserved chars or sub-delimiters
            ["/$unreserved?$unreserved#$unreserved", "/$unreserved", $unreserved, $unreserved, "/$unreserved?$unreserved#$unreserved"],
            // Encoded unreserved chars are not decoded
            ['/p%61th?q=v%61lue#fr%61gment', '/p%61th', 'q=v%61lue', 'fr%61gment', '/p%61th?q=v%61lue#fr%61gment'],
        ];
    }

    public function testWithPathEncodesProperly()
    {
        $uri = (new Uri())->withPath('/baz?#€/b%61r');
        // Query and fragment delimiters and multibyte chars are encoded.
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', $uri->getPath());
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', (string) $uri);
    }

    public function testWithQueryEncodesProperly()
    {
        $uri = (new Uri())->withQuery('?=#&€=/&b%61r');
        // A query starting with a "?" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the query.
        $this->assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        $this->assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    public function testWithFragmentEncodesProperly()
    {
        $uri = (new Uri())->withFragment('#€?/b%61r');
        // A fragment starting with a "#" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the fragment.
        $this->assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        $this->assertSame('#%23%E2%82%AC?/b%61r', (string) $uri);
    }

    public function testAllowsForRelativeUri()
    {
        $uri = (new Uri())->withPath('foo');
        $this->assertSame('foo', $uri->getPath());
        $this->assertSame('foo', (string) $uri);
    }

    public function testAddsSlashForRelativeUriStringWithHost()
    {
        // If the path is rootless and an authority is present, the path MUST
        // be prefixed by "/".
        $uri = (new Uri())->withPath('foo')->withHost('example.com');
        $this->assertSame('foo', $uri->getPath());
        // concatenating a relative path with a host doesn't work: "//example.comfoo" would be wrong
        $this->assertSame('//example.com/foo', (string) $uri);
    }

    public function testRemoveExtraSlashesWihoutHost()
    {
        // If the path is starting with more than one "/" and no authority is
        // present, the starting slashes MUST be reduced to one.
        $uri = (new Uri())->withPath('//foo');
        $this->assertSame('//foo', $uri->getPath());
        // URI "//foo" would be interpreted as network reference and thus change the original path to the host
        $this->assertSame('/foo', (string) $uri);
    }

    public function testDefaultReturnValuesOfGetters()
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

    public function testImmutability()
    {
        $uri = new Uri();

        $this->assertNotSame($uri, $uri->withScheme('https'));
        $this->assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        $this->assertNotSame($uri, $uri->withHost('example.com'));
        $this->assertNotSame($uri, $uri->withPort(8080));
        $this->assertNotSame($uri, $uri->withPath('/path/123'));
        $this->assertNotSame($uri, $uri->withQuery('q=abc'));
        $this->assertNotSame($uri, $uri->withFragment('test'));
    }

    public function testUtf8Host()
    {
        $uri = new Uri('http://ουτοπία.δπθ.gr/');
        $this->assertSame('ουτοπία.δπθ.gr', $uri->getHost());
        $new = $uri->withHost('程式设计.com');

        $this->assertSame('程式设计.com', $new->getHost());

        $testDomain = 'παράδειγμα.δοκιμή';
        $uri = (new Uri())->withHost($testDomain);
        $this->assertSame($testDomain, $uri->getHost());
        $this->assertSame('//' . $testDomain, (string) $uri);
    }
}