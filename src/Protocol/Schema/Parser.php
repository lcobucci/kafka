<?php
declare(strict_types=1);

namespace Lcobucci\Kafka\Protocol\Schema;

use Lcobucci\Kafka\Protocol\Schema;
use Lcobucci\Kafka\Protocol\Type;

use function array_key_exists;
use function is_string;

final class Parser
{
    /** @param array<string, class-string<Type>|array<string, mixed>> $definition */
    public function parse(array $definition): Schema
    {
        return new Schema(...$this->parseFields($definition));
    }

    /**
     * @param array<string, class-string<Type>|array<string, mixed>> $definition
     *
     * @return iterable<Field>
     */
    private function parseFields(array $definition): iterable
    {
        foreach ($definition as $name => $fieldDefinition) {
            yield new Field($name, $this->parseFieldType($fieldDefinition));
        }
    }

    /** @param class-string<Type>|array<string, mixed> $fieldDefinition */
    private function parseFieldType(string|array $fieldDefinition): Type
    {
        if (is_string($fieldDefinition)) {
            return new $fieldDefinition();
        }

        if (array_key_exists('_items', $fieldDefinition)) {
            return new Type\ArrayOf(
                $this->parseFieldType($fieldDefinition['_items']),
                $fieldDefinition['_nullable'] ?? false,
            );
        }

        return $this->parse($fieldDefinition);
    }
}
