<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use Lcobucci\Kafka\Exception;
use OverflowException;

final class NotEnoughBytesAllocated extends OverflowException implements Exception
{
    public static function forLength(int $length): self
    {
        return new self('It was not possible to read/write ' . $length . ' byte(s) from current position');
    }
}
