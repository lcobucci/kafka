<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol;

use Lcobucci\Kafka\Protocol\Type;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type
 */
final class TypeTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::isNullable
     */
    public function isNullableShouldReturnFalse(): void
    {
        $type = $this->getMockForAbstractClass(Type::class);

        self::assertFalse($type->isNullable());
    }
}
