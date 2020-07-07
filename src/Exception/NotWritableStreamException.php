<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use RuntimeException;
use Throwable;

class NotWritableStreamException extends RuntimeException
{
    protected $message = 'Unable to write to stream';
}