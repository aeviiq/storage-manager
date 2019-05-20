<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements IException
{
    public static function dataKeyDoesNotExist(object $subject, string $key): InvalidArgumentException
    {
        return new static(sprintf('"%s" does not have data stored with key "%s".', get_class($subject), $key));
    }

    public static function saveKeySameAsMasterKey(object $subject, string $key): InvalidArgumentException
    {
        return new static(sprintf('The save key "%s" cannot be the same as the master key in "%s".', get_class($subject), $key));
    }
}
