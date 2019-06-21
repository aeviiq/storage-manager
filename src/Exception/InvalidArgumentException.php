<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements Throwable
{
    public static function dataKeyDoesNotExist(object $subject, string $key): InvalidArgumentException
    {
        return new static(\sprintf('"%s" does not have data stored with key "%s".', \get_class($subject), $key));
    }

    public static function saveKeySameAsMasterKey(object $subject, string $key): InvalidArgumentException
    {
        return new static(\sprintf('The save key "%s" cannot be the same as the master key in "%s".', \get_class($subject), $key));
    }

    public static function masterKeyCanNotBeRemoved(object $subject, string $key): InvalidArgumentException
    {
        return new static(\sprintf('The master key "%s" in "%s" cannot be used in a remove(). Use the clear() instead.', \get_class($subject), $key));
    }
}
