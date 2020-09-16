<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Test\Unit\Protocol;

use Lcobucci\Kafka\Exception;
use Lcobucci\Kafka\Protocol\SchemaValidationFailure;
use Lcobucci\Kafka\Protocol\Type;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function get_class;

/** @coversDefaultClass \Lcobucci\Kafka\Protocol\Type */
final class TypeTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::isNullable
     */
    public function isNullableShouldReturnFalse(): void
    {
        $type = $this->createType();

        self::assertFalse($type->isNullable());
    }

    /**
     * @test
     *
     * @covers ::guardAgainstNull
     */
    public function guardAgainstNullShouldNotRaiseExceptionIfValueIsNotNull(): void
    {
        $type = $this->createType(['guardAgainstNull' => ['integer']]);
        $type->validate(0);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardAgainstNull
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::nullValue
     */
    public function guardAgainstNullShouldRaiseExceptionIfValueIsNull(): void
    {
        $type = $this->createType(['guardAgainstNull' => ['integer']]);

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('Provided value is null, expected type: integer');

        $type->validate(null);
    }

    /**
     * @test
     *
     * @covers ::guardType
     */
    public function guardTypeShouldNotRaiseExceptionIfValueHasExpectedType(): void
    {
        $type = $this->createType(['guardType' => ['integer', 'is_int']]);
        $type->validate(1);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardType
     */
    public function guardTypeShouldNotRaiseExceptionIfValuIsNull(): void
    {
        $type = $this->createType(['guardType' => ['integer', 'is_int']]);
        $type->validate(null);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardType
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::incorrectType
     */
    public function guardTypeShouldRaiseExceptionIfValueDoesNotHaveExpectedType(): void
    {
        $type = $this->createType(['guardType' => ['integer', 'is_int']]);

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('double(0.1) does not have expected type: integer');

        $type->validate(0.1);
    }

    /**
     * @test
     *
     * @covers ::guardClass
     */
    public function guardClassShouldNotRaiseExceptionIfValueIsInstanceOfExpectedClassOrInterface(): void
    {
        $type = $this->createType(['guardClass' => [Exception::class]]);
        $type->validate(new SchemaValidationFailure());

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardClass
     */
    public function guardClassShouldNotRaiseExceptionIfValueIsNull(): void
    {
        $type = $this->createType(['guardClass' => [Exception::class]]);
        $type->validate(null);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardClass
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::incorrectClass
     */
    public function guardClassShouldRaiseExceptionIfValueIsNotInstanceOfExpectedClassOrInterface(): void
    {
        $type = $this->createType(['guardClass' => [Exception::class]]);

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('Object (RuntimeException) is not an instance of: Lcobucci\Kafka\Exception');

        $type->validate(new RuntimeException());
    }

    /**
     * @test
     *
     * @covers ::guardRange
     */
    public function guardRangeShouldNotRaiseExceptionIfValueIsBetweenExpectedRange(): void
    {
        $type = $this->createType(['guardRange' => [1, 1]]);
        $type->validate(1);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardRange
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::incorrectRange
     */
    public function guardRangeShouldRaiseExceptionIfValueIsNotBetweenExpectedRange(): void
    {
        $type = $this->createType(['guardRange' => [1, 1]]);

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('0 is not between expected range: [1, 1]');

        $type->validate(0);
    }

    /**
     * @test
     *
     * @covers ::guardLength
     */
    public function guardLengthShouldNotRaiseExceptionIfValueLengthIsLessOrEqualThanMaximum(): void
    {
        $type = $this->createType(['guardLength' => [10]]);
        $type->validate('0123456789');

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardLength
     */
    public function guardLengthShouldNotRaiseExceptionIfValueIsNull(): void
    {
        $type = $this->createType(['guardLength' => [10]]);
        $type->validate(null);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     *
     * @covers ::guardLength
     * @covers \Lcobucci\Kafka\Protocol\SchemaValidationFailure::incorrectLength
     */
    public function guardLengthShouldRaiseExceptionIfValueLengthIsGreaterThanMaximum(): void
    {
        $type = $this->createType(['guardLength' => [10]]);

        $this->expectException(SchemaValidationFailure::class);
        $this->expectExceptionMessage('String length (11) is larger than the maximum length (10)');

        $type->validate('01234567891');
    }

    /** @param mixed[] $validationRules */
    private function createType(array $validationRules = []): Type
    {
        $type = $this->getMockForAbstractClass(Type::class);

        if ($validationRules === []) {
            return $type;
        }

        $callback = function ($data) use ($validationRules): void {
            foreach ($validationRules as $method => $arguments) {
                $this->$method($data, ...$arguments);
            }
        };

        $type->expects(self::once())
             ->method('validate')
             ->willReturnCallback($callback->bindTo($type, get_class($type)));

        return $type;
    }
}
