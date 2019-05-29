<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\Exception;

final class UnexpectedValueException extends \UnexpectedValueException implements IException
{
    public static function storageDataExpectedToBeObject(object $subject, string $key): UnexpectedValueException
    {
        return new static(\sprintf('The data with key "%s" in "%s" must be an object.', $key, \get_class($subject)));
    }

    public static function unableToLoadStorableEntity(string $subject, array $identifiers): UnexpectedValueException
    {
        return new static(
            \sprintf(
                'The entity "%s" with identifier(s) "%s" and value(s) "%s" could not be found by the given entity manager. ',
                $subject,
                \implode('", "', \array_keys($identifiers)),
                \implode('", "', \array_values($identifiers))
            )
        );
    }
}
