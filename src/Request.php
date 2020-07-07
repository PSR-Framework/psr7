<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Traits\MessageTrait;
use Psr\Http\Message\MessageInterface;

final class Request implements MessageInterface
{
    use MessageTrait;
}