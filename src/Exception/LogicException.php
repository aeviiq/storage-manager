<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\Exception;

final class LogicException extends \LogicException implements Throwable
{
    public static function entityMustBePersistedAndFlushed(string $entity): LogicException
    {
        return new static(\sprintf('The entity "%s" must be persisted and flushed before being saved.', $entity));
    }
}
