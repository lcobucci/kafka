<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int16;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\Int16
 */
final class Int16Test extends TestCase
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
        $buffer = Buffer::allocate(4);

        $type = new Int16();
        $type->write(-129, $buffer);
        $type->write(128, $buffer);

        $buffer->reset();

        self::assertSame(-129, $buffer->readShort());
        self::assertSame(128, $buffer->readShort());
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
        $type = new Int16();

        $this->expectException(ValueOutOfAllowedRange::class);
        $type->write(32768, Buffer::allocate(2));
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
        $buffer = Buffer::fromContent(pack('n2', -32768, 32767));
        $type   = new Int16();

        self::assertSame(-32768, $type->read($buffer));
        self::assertSame(32767, $type->read($buffer));
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
        $type = new Int16();

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
        $type = new Int16();

        self::assertSame(2, $type->sizeOf(-32768));
        self::assertSame(2, $type->sizeOf(32767));
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
        $type = new Int16();

        $type->validate(-32768);
        $type->validate(32767);

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
        $type = new Int16();

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
        $type = new Int16();

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
        $type = new Int16();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(-32769);
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
        $type = new Int16();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(32768);
    }
}
