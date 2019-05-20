<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\Exception;

final class UnexpectedValueException extends \UnexpectedValueException implements IException
{
    public static function storageDataExpectedToBeObject(object $subject, string $key): UnexpectedValueException
    {
        return new static(sprintf('The data with key "%s" in "%s" must be an object.', $key, get_class($subject)));
    }
}
