<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int64;
use PHPUnit\Framework\TestCase;

use function pack;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/** @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\Int64 */
final class Int64Test extends TestCase
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
        $buffer = Buffer::allocate(16);

        $type = new Int64();
        $type->write(PHP_INT_MIN, $buffer);
        $type->write(PHP_INT_MAX, $buffer);

        $buffer->reset();

        self::assertSame(PHP_INT_MIN, $buffer->readLong());
        self::assertSame(PHP_INT_MAX, $buffer->readLong());
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
        $type = new Int64();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write(PHP_INT_MIN, Buffer::allocate(0));
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
        $buffer = Buffer::fromContent(pack('J2', PHP_INT_MIN, PHP_INT_MAX));
        $type   = new Int64();

        self::assertSame(PHP_INT_MIN, $type->read($buffer));
        self::assertSame(PHP_INT_MAX, $type->read($buffer));
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
        $type = new Int64();

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
        $type = new Int64();

        self::assertSame(8, $type->sizeOf(PHP_INT_MIN));
        self::assertSame(8, $type->sizeOf(PHP_INT_MAX));
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
        $type = new Int64();

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
        $type = new Int64();

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
        $type = new Int64();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('integer');

        $type->validate(true);
    }
}
