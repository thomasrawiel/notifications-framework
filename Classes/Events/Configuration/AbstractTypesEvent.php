<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

abstract class AbstractTypesEvent
{
    /**
     * @var string[] List of valid types (non-empty strings or numerics)
     */
    private array $types;

    /**
     * Constructor.
     *
     * @param array $types Initial list of types
     */
    public function __construct(array $types)
    {
        $this->types = $this->sanitizeTypes($types);
    }

    /**
     * Returns the list of types.
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Sets the list of types, applying validation and sanitization.
     *
     * @param array $types
     */
    public function setTypes(array $types): void
    {
        $this->types = $this->sanitizeTypes($types);
    }

    /**
     * Adds a type to the list if it is valid and not already present.
     *
     * @param string|int|float $type
     */
    public function addType(string|int|float $type): void
    {
        if ($this->isValidType($type) && !in_array((string)$type, $this->types, true)) {
            $this->types[] = (string)$type;
        }
    }

    /**
     * Removes a type from the list (by string value match).
     *
     * @param string|int|float $type
     */
    public function removeType(string|int|float $type): void
    {
        $this->types = array_values(
            array_filter(
                $this->types,
                static fn($t) => (string)$t !== (string)$type
            )
        );
    }

    /**
     * Returns the types as a comma-separated string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(',', $this->types);
    }

    /**
     * Filters and converts an array to a valid list of type strings.
     *
     * @param array $types
     *
     * @return string[]
     */
    private function sanitizeTypes(array $types): array
    {
        return array_values(array_filter(
            array_map('strval', $types),
            fn($t) => $this->isValidType($t)
        ));
    }

    /**
     * Checks if a value is a valid type (non-empty string or numeric).
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isValidType(mixed $value): bool
    {
        return (is_string($value) || is_numeric($value)) && $value !== '';
    }
}