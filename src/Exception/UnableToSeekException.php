<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use RuntimeException;

class UnableToSeekException extends RuntimeException
{
    public function __construct(int $offset, int $whence, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = 'Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true);
    }
}