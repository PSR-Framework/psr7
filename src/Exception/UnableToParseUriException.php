<?php

declare(strict_types=1);

namespace Arslanoov\Psr7\Exception;

use Throwable;

class UnableToParseUriException extends InvalidArgumentException
{
    public function __construct(string $uri, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        if ($message === '') {
            $this->message = 'Unable to parse URI: ' . $uri;
        }
    }
}