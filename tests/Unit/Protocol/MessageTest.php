<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated;
use Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange;
use PHPUnit\Framework\TestCase;
use function bin2hex;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Message
 */
final class MessageTest extends TestCase
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
        $message = Message::allocate(2);

        self::assertSame("\0\0", $message->bytes());
        self::assertSame(2, $message->length());
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
        $message = Message::fromContent("\0\0");

        self::assertSame("\0\0", $message->bytes());
        self::assertSame(2, $message->length());
    }

    /**
     * @test
     *
     * @covers ::write
     * @covers ::nextIndex
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     */
    public function writeShouldModifyTheAllocatedBytesAndMoveThePosition(): void
    {
        $message = Message::allocate(1);
        $message->write('a');

        self::assertSame('a', $message->bytes());
        self::assertAttributeSame(1, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::write
     * @covers ::nextIndex
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     */
    public function writeShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(1);
        $message->write('a');

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $message->write('b');
    }

    /**
     * @test
     *
     * @covers ::read
     * @covers ::nextIndex
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::fromContent()
     */
    public function readShouldModifyThePositionAndReturnRequestedContent(): void
    {
        $message = Message::fromContent('a');

        self::assertSame('a', $message->read(1));
        self::assertAttributeSame(1, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::read
     * @covers ::nextIndex
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::fromContent()
     */
    public function readShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::fromContent('');

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $message->read(1);
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeByteShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $message = Message::allocate(2);
        $message->writeByte(-128);
        $message->writeByte(127);

        self::assertSame('807f', bin2hex($message->bytes()));
        self::assertAttributeSame(2, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeByteShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $message = Message::allocate(2);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-129) is out of the expected range [-128, 127]');
        $message->writeByte(-129);
    }

    /**
     * @test
     *
     * @covers ::writeByte
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeByteShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $message = Message::allocate(2);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (128) is out of the expected range [-128, 127]');
        $message->writeByte(128);
    }

    /**
     * @test
     *
     * @covers ::readByte
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::writeByte()
     * @uses \Lcobucci\Kafka\Protocol\Message::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readByteShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $message = Message::allocate(2);
        $message->writeByte(-128);
        $message->writeByte(127);
        $message->reset();

        self::assertSame(-128, $message->readByte());
        self::assertSame(127, $message->readByte());
    }

    /**
     * @test
     *
     * @covers ::readByte
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readByteShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 1 byte(s) from current position');
        $message->readByte();
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeShortShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $message = Message::allocate(4);
        $message->writeShort(-32768);
        $message->writeShort(32767);

        self::assertSame('80007fff', bin2hex($message->bytes()));
        self::assertAttributeSame(4, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeShortShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-32769) is out of the expected range [-32768, 32767]');
        $message->writeShort(-32769);
    }

    /**
     * @test
     *
     * @covers ::writeShort
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeShortShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (32768) is out of the expected range [-32768, 32767]');
        $message->writeShort(32768);
    }

    /**
     * @test
     *
     * @covers ::readShort
     * @covers ::reset
     * @covers ::convertToSigned
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::writeShort()
     * @uses \Lcobucci\Kafka\Protocol\Message::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readShortShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $message = Message::allocate(4);
        $message->writeShort(-32768);
        $message->writeShort(32767);
        $message->reset();

        self::assertSame(-32768, $message->readShort());
        self::assertSame(32767, $message->readShort());
    }

    /**
     * @test
     *
     * @covers ::readShort
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readShortShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 2 byte(s) from current position');
        $message->readShort();
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeIntShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $message = Message::allocate(8);
        $message->writeInt(-2147483648);
        $message->writeInt(2147483647);

        self::assertSame('800000007fffffff', bin2hex($message->bytes()));
        self::assertAttributeSame(8, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeIntShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-2147483649) is out of the expected range [-2147483648, 2147483647]');
        $message->writeInt(-2147483649);
    }

    /**
     * @test
     *
     * @covers ::writeInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeIntShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (2147483648) is out of the expected range [-2147483648, 2147483647]');
        $message->writeInt(2147483648);
    }

    /**
     * @test
     *
     * @covers ::readInt
     * @covers ::reset
     * @covers ::convertToSigned
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::writeInt()
     * @uses \Lcobucci\Kafka\Protocol\Message::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readIntShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $message = Message::allocate(8);
        $message->writeInt(-2147483648);
        $message->writeInt(2147483647);
        $message->reset();

        self::assertSame(-2147483648, $message->readInt());
        self::assertSame(2147483647, $message->readInt());
    }

    /**
     * @test
     *
     * @covers ::readInt
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readIntShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 4 byte(s) from current position');
        $message->readInt();
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeUnsignedIntShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $message = Message::allocate(8);
        $message->writeUnsignedInt(0);
        $message->writeUnsignedInt(4294967295);

        self::assertSame('00000000ffffffff', bin2hex($message->bytes()));
        self::assertAttributeSame(8, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeUnsignedIntShouldThrowExceptionWhenValueIsSmallerThanLowerBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (-1) is out of the expected range [0, 4294967295]');
        $message->writeUnsignedInt(-1);
    }

    /**
     * @test
     *
     * @covers ::writeUnsignedInt
     * @covers ::guardBounds
     * @covers \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeUnsignedIntShouldThrowExceptionWhenValueIsBiggerThanUpperBound(): void
    {
        $message = Message::allocate(4);

        $this->expectException(ValueOutOfAllowedRange::class);
        $this->expectExceptionMessage('Given value (4294967296) is out of the expected range [0, 4294967295]');
        $message->writeUnsignedInt(4294967296);
    }

    /**
     * @test
     *
     * @covers ::readUnsignedInt
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::writeUnsignedInt()
     * @uses \Lcobucci\Kafka\Protocol\Message::guardBounds()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readUnsignedIntShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $message = Message::allocate(8);
        $message->writeUnsignedInt(0);
        $message->writeUnsignedInt(4294967295);
        $message->reset();

        self::assertSame(0, $message->readUnsignedInt());
        self::assertSame(4294967295, $message->readUnsignedInt());
    }

    /**
     * @test
     *
     * @covers ::readUnsignedInt
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readUnsignedIntShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 4 byte(s) from current position');
        $message->readUnsignedInt();
    }

    /**
     * @test
     *
     * @covers ::writeLong
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::bytes()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function writeLongShouldAddTheBinaryValueOfTheDataAndMoveThePosition(): void
    {
        $message = Message::allocate(16);
        $message->writeLong((int) -9223372036854775808);
        $message->writeLong(9223372036854775807);

        self::assertSame('80000000000000007fffffffffffffff', bin2hex($message->bytes()));
        self::assertAttributeSame(16, 'position', $message);
    }

    /**
     * @test
     *
     * @covers ::readLong
     * @covers ::reset
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::write()
     * @uses \Lcobucci\Kafka\Protocol\Message::writeLong()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readLongShouldMovePositionAndReturnATheCorrectValue(): void
    {
        $message = Message::allocate(16);
        $message->writeLong((int) -9223372036854775808);
        $message->writeLong(9223372036854775807);
        $message->reset();

        self::assertSame((int) -9223372036854775808, $message->readLong());
        self::assertSame(9223372036854775807, $message->readLong());
    }

    /**
     * @test
     *
     * @covers ::readLong
     * @covers \Lcobucci\Kafka\Protocol\NotEnoughBytesAllocated
     *
     * @uses \Lcobucci\Kafka\Protocol\Message::__construct()
     * @uses \Lcobucci\Kafka\Protocol\Message::allocate()
     * @uses \Lcobucci\Kafka\Protocol\Message::read()
     * @uses \Lcobucci\Kafka\Protocol\Message::nextIndex()
     */
    public function readLongShouldRaiseExceptionWhenThereIsNotEnoughAllocatedBytes(): void
    {
        $message = Message::allocate(0);

        $this->expectException(NotEnoughBytesAllocated::class);
        $this->expectExceptionMessage('It was not possible to read/write 8 byte(s) from current position');
        $message->readLong();
    }
}
