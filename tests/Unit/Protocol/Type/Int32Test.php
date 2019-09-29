<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int32;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\Int32
 */
final class Int32Test extends TestCase
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

        $type = new Int32();
        $type->write(-32769, $buffer);
        $type->write(32768, $buffer);

        $buffer->reset();

        self::assertSame(-32769, $buffer->readInt());
        self::assertSame(32768, $buffer->readInt());
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
        $type = new Int32();

        $this->expectException(ValueOutOfAllowedRange::class);
        $type->write(2147483648, Buffer::allocate(4));
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
        $buffer = Buffer::fromContent(pack('N2', -2147483648, 2147483647));
        $type   = new Int32();

        self::assertSame(-2147483648, $type->read($buffer));
        self::assertSame(2147483647, $type->read($buffer));
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
        $type = new Int32();

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
        $type = new Int32();

        self::assertSame(4, $type->sizeOf(-2147483648));
        self::assertSame(4, $type->sizeOf(2147483647));
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsASignedNumber(): void
    {
        $type = new Int32();

        $type->validate(-2147483648);
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
        $type = new Int32();

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
        $type = new Int32();

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
    public function validateShouldRaiseExceptionWhenValueIsLesserThanASignedInt(): void
    {
        $type = new Int32();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(-2147483649);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsGreaterThanASignedInt(): void
    {
        $type = new Int32();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(2147483648);
    }
}
