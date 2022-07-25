<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol;

use Lcobucci\Kafka\Protocol\Buffer;
use Lcobucci\Kafka\Protocol\Schema;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type\Int8;
use Lcobucci\Kafka\Protocol\Type\NonNullableString;
use Lcobucci\Kafka\Protocol\Type\NullableString;
use PHPUnit\Framework\TestCase;

use function pack;

/** @coversDefaultClass \Lcobucci\Kafka\Protocol\Schema */
final class SchemaTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::write
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function writeShouldGoThroughEveryFieldAndAddDataToMessage(): void
    {
        $buffer = Buffer::allocate(2);
        $field1 = new Schema\Field('test1', new Int8());
        $field2 = new Schema\Field('test2', new Int8());

        $schema = new Schema($field1, $field2);
        $schema->write(['test2' => 10, 'test1' => 1], $buffer);

        $buffer->reset();

        self::assertSame(1, $field1->readFrom($buffer));
        self::assertSame(10, $field2->readFrom($buffer));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::read
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     */
    public function readShouldUseFieldDataToParseMessage(): void
    {
        $buffer = Buffer::fromContent(pack('c2', 1, 10));
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new Int8()),
        );

        self::assertSame(['test1' => 1, 'test2' => 10], $schema->read($buffer));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::sizeOf
     *
     * @uses \Lcobucci\Kafka\Protocol\Buffer
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\Type\NonNullableString
     */
    public function sizeOfShouldReturnTheNumberOfBytesNeededForEveryFieldInSchema(): void
    {
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new NonNullableString()),
        );

        self::assertSame(7, $schema->sizeOf(['test1' => 1, 'test2' => 'test']));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     * @covers ::validateField
     *
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\Type\NullableString
     */
    public function validateShouldNotRaiseExceptionWhenValueIsAnArrayWithAllRequiredValues(): void
    {
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new NullableString()),
        );

        $schema->validate(['test1' => 1, 'test2' => 'test']);
        $schema->validate(['test1' => 1, 'test2' => null]);
        $schema->validate(['test1' => 1]);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNull(): void
    {
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new Int8()),
        );

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('null');

        $schema->validate(null);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     *
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueIsNotAnArray(): void
    {
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new Int8()),
        );

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('array');

        $schema->validate(true);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::validate
     * @covers ::validateField
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::invalidValueForField
     *
     * @uses \Lcobucci\Kafka\Protocol\Schema\Field
     * @uses \Lcobucci\Kafka\Protocol\Type
     * @uses \Lcobucci\Kafka\Protocol\Type\Int8
     * @uses \Lcobucci\Kafka\Protocol\SchemaValidationFailure
     */
    public function validateShouldRaiseExceptionWhenValueOfOneOfTheFieldsIsInvalid(): void
    {
        $schema = new Schema(
            new Schema\Field('test1', new Int8()),
            new Schema\Field('test2', new Int8()),
        );

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('value for field "test2": 130 is not between expected range');
        $this->expectExceptionCode(0);

        $schema->validate(['test1' => 10, 'test2' => 130]);
    }
}
