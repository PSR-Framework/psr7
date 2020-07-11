<?php

declare(strict_types=1);

namespace tests\Furious\Psr7\Response;

use Furious\Psr7\Response\EmptyResponse;
use PHPUnit\Framework\TestCase;

class EmptyResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $response = new EmptyResponse();

        $this->assertEquals('', (string) $response->getBody());
        $this->assertEquals(204, $response->getStatusCode());
    }
}