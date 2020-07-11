<?php

declare(strict_types=1);

namespace Furious\Psr7\Exception;

use Throwable;
use UnexpectedValueException;

class UnownedProtocolVersion extends UnexpectedValueException
{
    public function __construct(string $protocolVersion, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = 'Unowned protocol version: ' . $protocolVersion;
    }
}