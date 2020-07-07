<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use RuntimeException;
use Throwable;

class NotReadableStreamException extends RuntimeException
{
    protected $message = 'Stream is not readable.';
}