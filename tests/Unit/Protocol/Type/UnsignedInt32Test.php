<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\UnsignedInt32;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\UnsignedInt32
 */
final class UnsignedInt32Test extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldAppendTwoBytesToMessageForGivenNumber(): void
    {
        $buffer = Buffer::allocate(8);

        $type = new UnsignedInt32();
        $type->write(0, $buffer);
        $type->write(4294967295, $buffer);

        $buffer->reset();

        self::assertSame(0, $buffer->readUnsignedInt());
        self::assertSame(4294967295, $buffer->readUnsignedInt());
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     */
    public function writeShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new UnsignedInt32();

        $this->expectException(ValueOutOfAllowedRange::class);
        $type->write(-1, Buffer::allocate(4));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function readShouldReturnSignedNumbers(): void
    {
        $buffer = Buffer::fromContent(pack('N2', 0, 4294967295));
        $type   = new UnsignedInt32();

        self::assertSame(0, $type->read($buffer));
        self::assertSame(4294967295, $type->read($buffer));
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
        $type = new UnsignedInt32();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Buffer::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     */
    public function sizeOfShouldAlwaysReturnTwo(): void
    {
        $type = new UnsignedInt32();

        self::assertSame(4, $type->sizeOf(0));
        self::assertSame(4, $type->sizeOf(4294967295));
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsAnUnsignedSignedNumber(): void
    {
        $type = new UnsignedInt32();

        $type->validate(0);
        $type->validate(2147483647);

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
        $type = new UnsignedInt32();

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
    public function validateShouldRaiseExceptionWhenValueIsNotAnInteger(): void
    {
        $type = new UnsignedInt32();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('integer');

        $type->validate(true);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsLesserThanAnUnsignedSignedInt(): void
    {
        $type = new UnsignedInt32();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(-1);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsGreaterThanAnUnsignedSignedInt(): void
    {
        $type = new UnsignedInt32();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(4294967297);
    }
}
