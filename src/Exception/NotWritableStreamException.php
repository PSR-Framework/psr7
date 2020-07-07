<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use Throwable;

class NotWritableStreamException extends RuntimeException
{
    public function __construct($message = 'Unable to write to stream', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}