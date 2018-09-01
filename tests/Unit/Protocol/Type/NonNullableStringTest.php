<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\NonNullableString;
use PHPUnit\Framework\TestCase;
use function pack;
use function str_repeat;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\NonNullableString
 */
final class NonNullableStringTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function writeShouldAppendTheLengthUsingTwoBytesAndTheStringContent(): void
    {
        $message = Message::allocate(6);

        $type = new NonNullableString();
        $type->write('test', $message);

        $message->reset();

        self::assertSame(4, $message->readShort());
        self::assertSame('test', $message->read(4));
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function writeShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new NonNullableString();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write('testing', Message::allocate(4));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function readShouldReturnTheContentBasedOnLength(): void
    {
        $message = Message::fromContent(pack('n', 4) . 'test' . pack('n', 7) . 'testing' . pack('n', 0));
        $type    = new NonNullableString();

        self::assertSame('test', $type->read($message));
        self::assertSame('testing', $type->read($message));
        self::assertSame('', $type->read($message));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function readShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new NonNullableString();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Message::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     */
    public function sizeOfShouldReturnTwoPlusLengthOfContent(): void
    {
        $type = new NonNullableString();

        self::assertSame(6, $type->sizeOf('test'));
        self::assertSame(9, $type->sizeOf('testing'));
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsString(): void
    {
        $type = new NonNullableString();

        $type->validate('test');
        $type->validate('testing');

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
        $type = new NonNullableString();

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
    public function validateShouldRaiseExceptionWhenValueIsNotAString(): void
    {
        $type = new NonNullableString();

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
        $type = new NonNullableString();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('length');

        $type->validate(str_repeat(' ', 32768));
    }
}
