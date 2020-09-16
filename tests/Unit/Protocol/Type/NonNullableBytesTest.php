<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Type;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\NonNullableBytes;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function pack;

/** @coversDefaultClass \Lcobucci\Kafka\Protocol\Type\NonNullableBytes */
final class NonNullableBytesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldAppendTheLengthUsingFourBytesAndTheContent(): void
    {
        $buffer = Buffer::allocate(8);

        $type = new NonNullableBytes();
        $type->write(Buffer::fromContent('1234'), $buffer);

        $buffer->reset();

        self::assertSame(4, $buffer->readInt());
        self::assertSame('1234', $buffer->read(4));
    }

    /**
     * @test
     *
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function writeShouldNotModifyContentsPosition(): void
    {
        $buffer  = Buffer::allocate(8);
        $content = Buffer::fromContent('1234');

        $type = new NonNullableBytes();
        $type->write($content, $buffer);

        self::assertSame(0, $content->position());
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
        $type = new NonNullableBytes();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->write(Buffer::fromContent('1234'), Buffer::allocate(4));
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
        $buffer = Buffer::fromContent(pack('N', 4) . 'test' . pack('N', 0));
        $type   = new NonNullableBytes();

        self::assertEquals(Buffer::fromContent('test'), $type->read($buffer));
        self::assertEquals(Buffer::fromContent(''), $type->read($buffer));
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
        $type = new NonNullableBytes();

        $this->expectException(NotEnoughBytesAllocated::class);
        $type->read(Buffer::allocate(0));
    }

    /**
     * @test
     *
     * @covers ::sizeOf
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function sizeOfShouldReturnFourPlusRemainingBytes(): void
    {
        $type = new NonNullableBytes();

        self::assertSame(6, $type->sizeOf(Buffer::allocate(2)));
        self::assertSame(9, $type->sizeOf(Buffer::allocate(5)));
    }

    /**
     * @test
     *
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     */
    public function validateShouldNotRaiseExceptionWhenValueIsAMessage(): void
    {
        $type = new NonNullableBytes();

        $type->validate(Buffer::fromContent('test'));
        $type->validate(Buffer::fromContent('testing'));

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
        $type = new NonNullableBytes();

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
    public function validateShouldRaiseExceptionWhenValueIsNotAnObject(): void
    {
        $type = new NonNullableBytes();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('object');

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
    public function validateShouldRaiseExceptionWhenValueIsNotAnInstanceOfMessage(): void
    {
        $type = new NonNullableBytes();

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage(Buffer::class);

        $type->validate(new RuntimeException());
    }
}
