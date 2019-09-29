<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;
use function bin2hex;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Buffer
 */
final class BufferTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::allocate
     * @covers ::__construct
     * @covers ::bytes
     * @covers ::length
     */
    public function allocateShouldInitialiseTheObjectWithNullContent(): void
    {
        $buffer = Buffer::allocate(2);

        self::assertSame("\0\0", $buffer->bytes());
        self::assertSame(2, $buffer->length());
    }

    /**
     * @test
     *
     * @covers ::fromContent
     * @covers ::__construct
     * @covers ::bytes
     * @covers ::length
     */
    public function fromContentShouldInitialiseMessageBasedOnGivenData(): void
    {
        $buffer = Buffer::fromContent("\0\0");

        self::assertSame("\0\0", $buffer->bytes());
        self::assertSame(2, $buffer->length());
    }

    /**
     * @test
     *
     * @covers ::remaining
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     */
    public function remainingShouldReturnTheNumberOfBytesLeftInTheMessage(): void
    {
        $buffer = Buffer::fromContent("\0\0");

        self::assertSame(2, $buffer->remaining());

        $buffer->read(1);

        self::assertSame(1, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::position
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function positionShouldReturnTheNumberOfReadBytes(): void
    {
        $buffer = Buffer::fromContent("\0\0");

        self::assertSame(0, $buffer->position());

        $buffer->read(1);

        self::assertSame(1, $buffer->position());
    }

    /**
     * @test
     *
     * @covers ::get
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::position()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     */
    public function getShouldReturnTheRequestedBytesWithoutChangingThePosition(): void
    {
        $buffer = Buffer::fromContent("\0\0");

        self::assertSame("\0", $buffer->get(0, 1));
        self::assertSame("\0\0", $buffer->get(0, 2));
        self::assertSame(0, $buffer->position());

        self::assertSame("\0", $buffer->read(1));
        self::assertSame(1, $buffer->position());

        self::assertSame("\0", $buffer->get(0));
        self::assertSame(1, $buffer->position());
    }

    /**
     * @test
     *
     * @covers ::get
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::position()
     */
    public function getShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::fromContent("\0");

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 2 byte(s) from current position');

        $buffer->get(2, 2);
    }

    /**
     * @test
     *
     * @covers ::write
     * @covers ::nextIndex
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeShouldModifyTheAllocatedBytesAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(1);
        $buffer->write('a');

        self::assertSame('a', $buffer->bytes());
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::write
     * @covers ::nextIndex
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(1);
        $buffer->write('a');

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $buffer->write('b');
    }

    /**
     * @test
     *
     * @covers ::read
     * @covers ::nextIndex
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readShouldModifyThePositionAndReturnRequestedContent(): void
    {
        $buffer = Buffer::fromContent('a');

        self::assertSame('a', $buffer->read(1));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::read
     * @covers ::nextIndex
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::fromContent()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::fromContent('');

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $buffer->read(1);
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeByteShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(2);
        $buffer->writeByte(-128);
        $buffer->writeByte(127);

        self::assertSame('807f', bin2hex($buffer->bytes()));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeByteShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $buffer = Buffer::allocate(2);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-129) is out of the expected range [-128, 127]');
        $buffer->writeByte(-129);
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeByteShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $buffer = Buffer::allocate(2);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (128) is out of the expected range [-128, 127]');
        $buffer->writeByte(128);
    }

    /**
     * @test
     *
     * @covers ::readByte
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::writeByte()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readByteShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $buffer = Buffer::allocate(2);
        $buffer->writeByte(-128);
        $buffer->writeByte(127);
        $buffer->reset();

        self::assertSame(-128, $buffer->readByte());
        self::assertSame(127, $buffer->readByte());
    }

    /**
     * @test
     *
     * @covers ::readByte
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readByteShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $buffer->readByte();
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeShortShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(4);
        $buffer->writeShort(-32768);
        $buffer->writeShort(32767);

        self::assertSame('80007fff', bin2hex($buffer->bytes()));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeShortShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-32769) is out of the expected range [-32768, 32767]');
        $buffer->writeShort(-32769);
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeShortShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (32768) is out of the expected range [-32768, 32767]');
        $buffer->writeShort(32768);
    }

    /**
     * @test
     *
     * @covers ::readShort
     * @covers ::reset
     * @covers ::convertToSigned
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::writeShort()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readShortShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $buffer = Buffer::allocate(4);
        $buffer->writeShort(-32768);
        $buffer->writeShort(32767);
        $buffer->reset();

        self::assertSame(-32768, $buffer->readShort());
        self::assertSame(32767, $buffer->readShort());
    }

    /**
     * @test
     *
     * @covers ::readShort
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readShortShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 2 byte(s) from current position');
        $buffer->readShort();
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeIntShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(8);
        $buffer->writeInt(-2147483648);
        $buffer->writeInt(2147483647);

        self::assertSame('800000007fffffff', bin2hex($buffer->bytes()));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeIntShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-2147483649) is out of the expected range [-2147483648, 2147483647]');
        $buffer->writeInt(-2147483649);
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeIntShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (2147483648) is out of the expected range [-2147483648, 2147483647]');
        $buffer->writeInt(2147483648);
    }

    /**
     * @test
     *
     * @covers ::readInt
     * @covers ::reset
     * @covers ::convertToSigned
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::writeInt()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readIntShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $buffer = Buffer::allocate(8);
        $buffer->writeInt(-2147483648);
        $buffer->writeInt(2147483647);
        $buffer->reset();

        self::assertSame(-2147483648, $buffer->readInt());
        self::assertSame(2147483647, $buffer->readInt());
    }

    /**
     * @test
     *
     * @covers ::readInt
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readIntShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 4 byte(s) from current position');
        $buffer->readInt();
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeUnsignedIntShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(8);
        $buffer->writeUnsignedInt(0);
        $buffer->writeUnsignedInt(4294967295);

        self::assertSame('00000000ffffffff', bin2hex($buffer->bytes()));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeUnsignedIntShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-1) is out of the expected range [0, 4294967295]');
        $buffer->writeUnsignedInt(-1);
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeUnsignedIntShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $buffer = Buffer::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (4294967296) is out of the expected range [0, 4294967295]');
        $buffer->writeUnsignedInt(4294967296);
    }

    /**
     * @test
     *
     * @covers ::readUnsignedInt
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::writeUnsignedInt()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readUnsignedIntShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $buffer = Buffer::allocate(8);
        $buffer->writeUnsignedInt(0);
        $buffer->writeUnsignedInt(4294967295);
        $buffer->reset();

        self::assertSame(0, $buffer->readUnsignedInt());
        self::assertSame(4294967295, $buffer->readUnsignedInt());
    }

    /**
     * @test
     *
     * @covers ::readUnsignedInt
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readUnsignedIntShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 4 byte(s) from current position');
        $buffer->readUnsignedInt();
    }

    /**
     * @test
     *
     * @covers ::writeLong
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function writeLongShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $buffer = Buffer::allocate(16);
        $buffer->writeLong((int) -9223372036854775808);
        $buffer->writeLong(9223372036854775807);

        self::assertSame('80000000000000007fffffffffffffff', bin2hex($buffer->bytes()));
        self::assertSame(0, $buffer->remaining());
    }

    /**
     * @test
     *
     * @covers ::readLong
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::write()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::writeLong()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readLongShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $buffer = Buffer::allocate(16);
        $buffer->writeLong((int) -9223372036854775808);
        $buffer->writeLong(9223372036854775807);
        $buffer->reset();

        self::assertSame((int) -9223372036854775808, $buffer->readLong());
        self::assertSame(9223372036854775807, $buffer->readLong());
    }

    /**
     * @test
     *
     * @covers ::readLong
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::read()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::nextIndex()
     * @uses \Lcobucci\Kafka\Protocol\Buffer::remaining()
     */
    public function readLongShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $buffer = Buffer::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 8 byte(s) from current position');
        $buffer->readLong();
    }
}
