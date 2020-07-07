<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use Throwable;

class IncorrectStreamPositionException extends RuntimeException
{
    public function __construct($message = 'Incorrect stream position.', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}