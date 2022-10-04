<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Exception;

final class LogicException extends \LogicException implements ExceptionInterface
{
    public static function entityMustBePersistedAndFlushed(string $entity): LogicException
    {
        return new self(\sprintf('The entity "%s" must be persisted and flushed in order to be detached.', $entity));
    }

    public static function entityPropertyIdIsMissing(): LogicException
    {
        return new self('Required entity property id is missing. Did you use the correct Matcher?');
    }
}
