<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol\Schema;

use Lcobucci\Kafka\Protocol\Message;
use Lcobucci\Kafka\Protocol\Schema\Field;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int8;
use Lcobucci\Kafka\Protocol\Type\NullableString;
use PHPUnit\Framework\TestCase;
use function pack;

/**
 * @coversDefaultClass \Lcobucci\Kafka\Protocol\Schema\Field
 */
final class FieldTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::name
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function nameShouldReturnTheFieldName(): void
    {
        $field = new Field('testing', new Int8());

        self::assertSame('testing', $field->name());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::writeTo
     * @covers ::extractValue
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::missingField
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function writeToShouldRaiseExceptionWhenFieldIsMissing(): void
    {
        $field = new Field('testing', new Int8());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('Field "testing" missing from given structure');

        $field->writeTo([], Message::allocate(1));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::writeTo
     * @covers ::extractValue
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function writeToShouldUseTypeToWriteIntoMessage(): void
    {
        $type  = new Int8();
        $field = new Field('testing', $type);

        $message = Message::allocate(1);
        $field->writeTo(['testing' => 10], $message);
        $message->reset();

        self::assertSame(10, $type->read($message));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::writeTo
     * @covers ::extractValue
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\NullableString
     */
    public function writeToShoulNotRaiseExceptionWhenFieldIsMissingButTypeIsNullable(): void
    {
        $type  = new NullableString();
        $field = new Field('testing', $type);

        $message = Message::allocate(2);
        $field->writeTo([], $message);
        $message->reset();

        self::assertNull($type->read($message));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::readFrom
     *
     * @uses \Lcobucci\Kafka\Protocol\Message
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function readFromShouldUseTheTypeToReadFromTheMessage(): void
    {
        $message = Message::fromContent(pack('c', 10));
        $field   = new Field('testing', new Int8());

        self::assertSame(10, $field->readFrom($message));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::sizeOf
     * @covers ::extractValue
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::missingField
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function sizeOfShouldRaiseExceptionWhenFieldIsMissing(): void
    {
        $field = new Field('testing', new Int8());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('Field "testing" missing from given structure');

        $field->sizeOf([]);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::sizeOf
     * @covers ::extractValue
     *
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function sizeOfShouldUseTypeToTheCalculateSize(): void
    {
        $type  = new Int8();
        $field = new Field('testing', $type);

        self::assertSame($type->sizeOf(10), $field->sizeOf(['testing' => 10]));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     * @covers ::extractValue
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::missingField
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function validateShouldRaiseExceptionWhenFieldIsMissing(): void
    {
        $field = new Field('testing', new Int8());

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('Field "testing" missing from given structure');

        $field->validate([]);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     * @covers ::extractValue
     *
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure::incorrectRange
     */
    public function validateShouldUseTypeToPerformValidation(): void
    {
        $field = new Field('testing', new Int8());

        $field->validate(['testing' => 10]);

        $this->expectException(SchemaValidationFailure::class);
        $field->validate(['testing' => 130]);
    }
}
