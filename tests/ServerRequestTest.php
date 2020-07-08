<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7;

use Arslanoov\Psr7\ServerRequest;
use Arslanoov\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    public function testQueryParams(): void
    {
        $firstRequest = new ServerRequest('GET', '/');

        $params = ['foo' => 'bar'];

        $secondRequest = $firstRequest->withQueryParams($params);

        $this->assertNotSame($secondRequest, $firstRequest);
        $this->assertEmpty($firstRequest->getQueryParams());
        $this->assertSame($params, $secondRequest->getQueryParams());
    }

    public function testServerParams(): void
    {
        $params = ['foo' => 'bar'];

        $request = new ServerRequest('GET', '/', [], null, '1.1', $params);
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