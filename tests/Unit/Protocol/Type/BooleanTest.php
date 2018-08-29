<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
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
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function writeShouldAppendASingleByteToMessageWithEitherZeroOrOne(): void
    {
        $message = Message::allocate(2);

        $type = new Boolean();
        $type->write(true, $message);
        $type->write(false, $message);

        $message->reset();

        self::assertSame(1, $message->readByte());
        self::assertSame(0, $message->readByte());
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function writeShouldConvertAnythingDifferentThanTrueToFalse(): void
    {
        $message = Message::allocate(1);

        $type = new Boolean();
        $type->write('anything', $message);

        $message->reset();

        self::assertSame(0, $message->readByte());
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
        $type = new Boolean();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write(true, Message::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function readShouldConvertZeroAndOneToTheirBooleanValue(): void
    {
        $message = Message::fromContent(pack('c2', 0, 1));
        $type    = new Boolean();

        self::assertFalse($type->read($message));
        self::assertTrue($type->read($message));
    }

    /**
     * @test
     *
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     */
    public function readShouldConvertAnyNumberDifferentThanZeroToTrue(): void
    {
        $message = Message::fromContent(pack('c2', -1, 2));
        $type    = new Boolean();

        self::assertTrue($type->read($message));
        self::assertTrue($type->read($message));
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
        $type = new Boolean();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Message::allocate(0));
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
