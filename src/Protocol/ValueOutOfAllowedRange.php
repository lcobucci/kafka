<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use Lcobucci\Kafka\Exception;
use RangeException;

use function sprintf;

final class ValueOutOfAllowedRange extends RangeException implements Exception
{
    public static function forRange(int $value, int $lowerBound, int $upperBound): self
    {
        return new self(
            sprintf(
                'Given value (%s) is out of the expected range [%s, %s]',
                $value,
                $lowerBound,
                $upperBound
            )
        );
    }
}
