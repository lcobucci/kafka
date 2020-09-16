<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int8;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;

use function pack;

/** @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\Int8 */
final class Int8Test extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldAppendASingleByteToMessageForGivenNumber(): void
    {
        $buffer = Buffer::allocate(2);

        $type = new Int8();
        $type->write(-128, $buffer);
        $type->write(127, $buffer);

        $buffer->reset();

        self::assertSame(-128, $buffer->readByte());
        self::assertSame(127, $buffer->readByte());
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
        $type = new Int8();

        $this->expectException(ValueOutOfAllowedRange::class);
        $type->write(128, Buffer::allocate(1));
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
        $buffer = Buffer::fromContent(pack('c2', -128, 127));
        $type   = new Int8();

        self::assertSame(-128, $type->read($buffer));
        self::assertSame(127, $type->read($buffer));
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
        $type = new Int8();

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
        $type = new Int8();

        self::assertSame(1, $type->sizeOf(-127));
        self::assertSame(1, $type->sizeOf(128));
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
        $type = new Int8();

        $type->validate(-128);
        $type->validate(127);

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
        $type = new Int8();

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
        $type = new Int8();

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
    public function validateShouldRaiseExceptionWhenValueIsLesserThanASignedNumber(): void
    {
        $type = new Int8();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(-129);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsGreaterThanASignedNumber(): void
    {
        $type = new Int8();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('between expected range');

        $type->validate(128);
    }
}
