<?php

declare(strict_types=1);

namespace tests\Arslanoov\Psr7\Factory;

use Arslanoov\Psr7\Factory\ServerRequestFactory;
use Arslanoov\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $uploadedFile = (new ServerRequestFactory())->createServerRequest('s', '/home', [
            'foo' => 'bar'
        ]);

        $this->assertInstanceOf(ServerRequestInterface::class, $uploadedFile);
        $this->assertInstanceOf(ServerRequest::class, $uploadedFile);
    }
}