<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Boolean;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\Boolean
 */
final class BooleanTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldAppendASingleByteToMessageWithEitherZeroOrOne(): void
    {
        $buffer = Buffer::allocate(2);

        $type = new Boolean();
        $type->write(true, $buffer);
        $type->write(false, $buffer);

        $buffer->reset();

        self::assertSame(1, $buffer->readByte());
        self::assertSame(0, $buffer->readByte());
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldConvertAnythingDifferentThanTrueToFalse(): void
    {
        $buffer = Buffer::allocate(1);

        $type = new Boolean();
        $type->write('anything', $buffer);

        $buffer->reset();

        self::assertSame(0, $buffer->readByte());
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function writeShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new Boolean();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write(true, Buffer::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function readShouldConvertZeroAndOneToTheirBooleanValue(): void
    {
        $buffer = Buffer::fromContent(pack('c2', 0, 1));
        $type   = new Boolean();

        self::assertFalse($type->read($buffer));
        self::assertTrue($type->read($buffer));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function readShouldConvertAnyNumberDifferentThanZeroToTrue(): void
    {
        $buffer = Buffer::fromContent(pack('c2', -1, 2));
        $type   = new Boolean();

        self::assertTrue($type->read($buffer));
        self::assertTrue($type->read($buffer));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function readShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new Boolean();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Buffer::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     */
    public function sizeOfShouldAlwaysReturnOne(): void
    {
        $type = new Boolean();

        self::assertSame(1, $type->sizeOf(false));
        self::assertSame(1, $type->sizeOf(true));
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsBoolean(): void
    {
        $type = new Boolean();

        $type->validate(true);
        $type->validate(false);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNull(): void
    {
        $type = new Boolean();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('null');

        $type->validate(null);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNotBoolean(): void
    {
        $type = new Boolean();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('boolean');

        $type->validate(1);
    }
}
