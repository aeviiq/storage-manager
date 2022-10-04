<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function dataKeyDoesNotExist(string $key): InvalidArgumentException
    {
        return new self(\sprintf('Data with key "%s" does not exist.', $key));
    }

    public static function saveKeySameAsMasterKey(string $key): InvalidArgumentException
    {
        return new self(\sprintf('The save key "%s" cannot be the same as the master key.', $key));
    }

    public static function masterKeyCanNotBeRemoved(string $key): InvalidArgumentException
    {
        return new self(\sprintf('The master key "%s" cannot be used in a single remove.', $key));
    }

    public static function invalidArgumentType(string $expectedType, mixed $actual): InvalidArgumentException
    {
        return new self(\sprintf('Expected type "%s", "%s" given.', $expectedType, gettype($actual)));
    }
}
