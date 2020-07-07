<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use RuntimeException;
use Throwable;

class IncorrectStreamPositionException extends RuntimeException
{
    protected $message = 'Incorrect stream position.';
}