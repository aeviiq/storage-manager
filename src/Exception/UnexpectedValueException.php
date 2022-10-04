<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Exception;

final class UnexpectedValueException extends \UnexpectedValueException implements ExceptionInterface
{
    public static function storageDataExpectedToBeObject(string $key): UnexpectedValueException
    {
        return new self(\sprintf('Data with key "%s" must be an object.', $key));
    }

    /**
     * @param array<mixed> $identifiers
     */
    public static function unableToLoadStorableEntity(string $subject, array $identifiers): UnexpectedValueException
    {
        return new self(
            \sprintf(
                'The entity "%s" with identifier(s) "%s" and value(s) "%s" could not be found by the given entity manager. ',
                $subject,
                \implode('", "', \array_keys($identifiers)),
                \implode(
                    '", "',
                    \array_values(
                        \array_filter($identifiers, static function ($value) {
                            return \is_scalar($value);
                        })
                    )
                )
            )
        );
    }

    public static function entityIdentifiersAreNotScalarType(mixed $value): UnexpectedValueException
    {
        return new self(\sprintf('Entity identifiers are expected to be of type scalar, "%s" given.', \gettype($value)));
    }
}
