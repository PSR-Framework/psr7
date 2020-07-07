<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use RuntimeException;
use Throwable;

class NotSeekableStreamException extends RuntimeException
{
    protected $message = 'Stream is not seekable.';
}