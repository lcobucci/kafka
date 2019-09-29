<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\NullableString;
use PHPUnit\Framework\TestCase;
use function pack;
use function str_repeat;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\NullableString
 */
final class NullableStringTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldAppendTheLengthUsingTwoBytesAndTheStringContent(): void
    {
        $buffer = Buffer::allocate(6);

        $type = new NullableString();
        $type->write('test', $buffer);

        $buffer->reset();

        self::assertSame(4, $buffer->readShort());
        self::assertSame('test', $buffer->read(4));
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldUseMinusOneForLengthWhenContentIsNull(): void
    {
        $buffer = Buffer::allocate(2);

        $type = new NullableString();
        $type->write(null, $buffer);

        $buffer->reset();

        self::assertSame(-1, $buffer->readShort());
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
        $type = new NullableString();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write('testing', Buffer::allocate(4));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function readShouldReturnTheContentBasedOnLength(): void
    {
        $buffer = Buffer::fromContent(pack('n', 4) . 'test' . pack('n', 0) . pack('n', -1));
        $type   = new NullableString();

        self::assertSame('test', $type->read($buffer));
        self::assertSame('', $type->read($buffer));
        self::assertNull($type->read($buffer));
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
        $type = new NullableString();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Buffer::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     */
    public function sizeOfShouldReturnTwoPlusLengthOfContent(): void
    {
        $type = new NullableString();

        self::assertSame(6, $type->sizeOf('test'));
        self::assertSame(9, $type->sizeOf('testing'));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     */
    public function sizeOfShouldReturnTwoWhenContentIsNull(): void
    {
        $type = new NullableString();

        self::assertSame(2, $type->sizeOf(null));
    }

    /**
     * @test
     *
     * @covers ::isNullable
     */
    public function isNullableShouldAlwaysReturnTrue(): void
    {
        $type = new NullableString();

        self::assertTrue($type->isNullable());
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsStringOrNull(): void
    {
        $type = new NullableString();

        $type->validate('test');
        $type->validate('testing');
        $type->validate(null);

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
    public function validateShouldRaiseExceptionWhenValueIsNotAString(): void
    {
        $type = new NullableString();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('string');

        $type->validate(false);
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenStringIsTooBig(): void
    {
        $type = new NullableString();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('length');

        $type->validate(str_repeat(' ', 32768));
    }
}
