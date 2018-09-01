<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol;

use function pack;
use function str_repeat;
use function strlen;
use function substr;
use function substr_replace;
use function unpack;

/**
 * Represents Kafka's binary message, providing ways to write and read content using big-endian byte order for
 * the basic data types required by kafka
 */
final class Message
{
    private const EMPTY_CONTENT = "\0";
    private const BYTE_RANGE    = [-2 ** 7, 2 ** 7 - 1];
    private const SHORT_RANGE   = [-2 ** 15, 2 ** 15 - 1];
    private const INT_RANGE     = [-2 ** 31, 2 ** 31 - 1];
    private const UINT_RANGE    = [0, 2 ** 32 - 1];

    private const CONVERSION_SHORT = [2 ** 15 - 1, 2 ** 16];
    private const CONVERSION_INT   = [2 ** 31 - 1, 2 ** 32];

    /**
     * @var string
     */
    private $bytes;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $position = 0;

    private function __construct(string $bytes, int $length)
    {
        $this->bytes  = $bytes;
        $this->length = $length;
    }

    /**
     * Creates an object with null bytes with given size
     */
    public static function allocate(int $length): self
    {
        return new self(str_repeat(self::EMPTY_CONTENT, $length), $length);
    }

    /**
     * Creates an object with given content, calculating the length properly
     */
    public static function fromContent(string $content): self
    {
        return new self($content, strlen($content));
    }

    /**
     * Returns all bytes of the message (in binary format)
     */
    public function bytes(): string
    {
        return $this->bytes;
    }

    /**
     * Returns the length of allocated bytes
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * Resets the position to its initial state (for reading/writing)
     */
    public function reset(): void
    {
        $this->position = 0;
    }

    /**
     * Returns the current offset to start reading/writing and moves the cursor by given length
     *
     * @throws NotEnoughBytesAllocated When trying to read/write from/into an invalid position.
     */
    private function nextIndex(int $length): int
    {
        if ($this->length - $this->position < $length) {
            throw NotEnoughBytesAllocated::forLength($length);
        }

        $currentPosition = $this->position;
        $this->position += $length;

        return $currentPosition;
    }

    /**
     * Overwrites allocated bytes in the current position
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     */
    public function write(string $value): void
    {
        $length = strlen($value);
        $offset = $this->nextIndex($length);

        $this->bytes = substr_replace($this->bytes, $value, $offset, $length);
    }

    /**
     * Reads an amount of bytes from the current position
     *
     * @return mixed
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function read(int $length)
    {
        $offset = $this->nextIndex($length);

        return substr($this->bytes, $offset, $length);
    }

    /**
     * Writes a single numeric byte (from -128 to 127)
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     * @throws ValueOutOfAllowedRange When value is not between a signed byte range (-2^7, 2^7 - 1).
     */
    public function writeByte(int $value): void
    {
        $this->guardBounds($value, ...self::BYTE_RANGE);
        $this->write(pack('c', $value));
    }

    /**
     * Validates if given value is between given bounds (inclusive)
     *
     * @throws ValueOutOfAllowedRange When value is not between the given range.
     */
    private function guardBounds(int $value, int $lowerBound, int $upperBound): void
    {
        if ($value < $lowerBound || $value > $upperBound) {
            throw ValueOutOfAllowedRange::forRange($value, $lowerBound, $upperBound);
        }
    }

    /**
     * Reads a single numeric byte (from -128 to 127)
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function readByte(): int
    {
        return unpack('c', $this->read(1))[1];
    }

    /**
     * Writes a short number (from -32768 to 32767)
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     * @throws ValueOutOfAllowedRange When value is not between a signed short range (-2^15, 2^15 - 1).
     */
    public function writeShort(int $value): void
    {
        $this->guardBounds($value, ...self::SHORT_RANGE);
        $this->write(pack('n', $value));
    }

    /**
     * Reads a short number (from -32768 to 32767)
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function readShort(): int
    {
        return $this->convertToSigned(unpack('n', $this->read(2))[1], ...self::CONVERSION_SHORT);
    }

    /**
     * Converts an unsigned number into a signed one based on given values
     */
    private function convertToSigned(int $value, int $maxValue, int $subtract): int
    {
        if ($value <= $maxValue) {
            return $value;
        }

        return $value - $subtract;
    }

    /**
     * Writes a 32-bit integer (from -2147483648 to 2147483647)
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     * @throws ValueOutOfAllowedRange When value is not between a signed integer range (-2^31, 2^31 - 1).
     */
    public function writeInt(int $value): void
    {
        $this->guardBounds($value, ...self::INT_RANGE);
        $this->write(pack('N', $value));
    }

    /**
     * Reads a 32-bit integer (from -2147483648 to 2147483647)
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function readInt(): int
    {
        return $this->convertToSigned(unpack('N', $this->read(4))[1], ...self::CONVERSION_INT);
    }

    /**
     * Writes an unsigned 32-bit integer (from 0 to 4294967295)
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     * @throws ValueOutOfAllowedRange When value is not between an unsigned integer range (0, 2^32 - 1).
     */
    public function writeUnsignedInt(int $value): void
    {
        $this->guardBounds($value, ...self::UINT_RANGE);
        $this->write(pack('N', $value));
    }

    /**
     * Reads an unsigned 32-bit integer (from 0 to 4294967295)
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function readUnsignedInt(): int
    {
        return unpack('N', $this->read(4))[1];
    }

    /**
     * Writes a 64-bit integer (from -9223372036854775808 to 9223372036854775807)
     *
     * Can't throw \Lcobucci\Kafka\Protocol\ValueOutOfAllowedRange due to type declarations (PHP will convert the number
     * to a double if it's out of bounds, and strict types will force an error).
     *
     * @throws NotEnoughBytesAllocated When trying to write into an invalid position.
     */
    public function writeLong(int $value): void
    {
        $this->write(pack('J', $value));
    }

    /**
     * Reads a 64-bit integer (from -9223372036854775808 to 9223372036854775807)
     *
     * Doesn't need to be converted to signed since PHP doesn't support unsigned 64-bit integers.
     *
     * @throws NotEnoughBytesAllocated When trying to read from an invalid position.
     */
    public function readLong(): int
    {
        return unpack('J', $this->read(8))[1];
    }
}
