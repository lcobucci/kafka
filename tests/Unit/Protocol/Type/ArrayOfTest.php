<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\ArrayOf;
use Lcobucci\Kafka\Protocol\Type\Boolean;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\ArrayOf
 */
final class ArrayOfTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function writeShouldAppendArrayCountAndEverySingleItem(): void
    {
        $message = Message::allocate(6);

        $type = new ArrayOf(new Boolean());
        $type->write([true, false], $message);

        $message->reset();

        self::assertSame(2, $message->readInt());
        self::assertSame(1, $message->readByte());
        self::assertSame(0, $message->readByte());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function writeShouldAppendMinusOneWhenDataIsNull(): void
    {
        $message = Message::allocate(4);

        $type = new ArrayOf(new Boolean());
        $type->write(null, $message);

        $message->reset();

        self::assertSame(-1, $message->readInt());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function writeShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new ArrayOf(new Boolean());

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write([], Message::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function readShouldReturnConvertedData(): void
    {
        $message = Message::fromContent(pack('Nc2N', 2, 1, 0, 0));
        $type    = new ArrayOf(new Boolean());

        self::assertSame([true, false], $type->read($message));
        self::assertSame([], $type->read($message));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function readShouldReturnNullWhenCountIsMinusOne(): void
    {
        $message = Message::fromContent(pack('N', -1));
        $type    = new ArrayOf(new Boolean());

        self::assertNull($type->read($message));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     */
    public function readShouldNotHandleExceptionsFromMessage(): void
    {
        $type = new ArrayOf(new Boolean());

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Message::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::isNullable
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function isNullableShouldReturnConfiguredValue(): void
    {
        self::assertFalse((new ArrayOf(new Boolean()))->isNullable());
        self::assertTrue((new ArrayOf(new Boolean(), true))->isNullable());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::sizeOf
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     */
    public function sizeOfShouldFourPlusTheSizeOfGivenTypeForEachElement(): void
    {
        $type = new ArrayOf(new Boolean());

        self::assertSame(4, $type->sizeOf(null));
        self::assertSame(4, $type->sizeOf([]));
        self::assertSame(5, $type->sizeOf([true]));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\Type
     */
    public function validateShouldNotRaiseExceptionWhenValueIsAnArrayOfCorrectTypeOrNull(): void
    {
        $type = new ArrayOf(new Boolean(), true);

        $type->validate([]);
        $type->validate([true, false]);
        $type->validate(null);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNullAndItIsNotNullable(): void
    {
        $type = new ArrayOf(new Boolean());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('null');

        $type->validate(null);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNotAnArray(): void
    {
        $type = new ArrayOf(new Boolean());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('array');

        $type->validate(1);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Boolean
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenArrayItemsAreNotValidatable(): void
    {
        $type = new ArrayOf(new Boolean());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('boolean');

        $type->validate([1]);
    }
}
