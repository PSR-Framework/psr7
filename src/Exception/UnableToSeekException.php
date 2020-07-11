<?php

declare(strict_types=1);

namespace Furious\Psr7\Exception;

use Throwable;

class UnableToSeekException extends RuntimeException
{
    public function __construct(int $offset, int $whence, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        if ($message === '') {
            $this->message = 'Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true);
        }
    }
}