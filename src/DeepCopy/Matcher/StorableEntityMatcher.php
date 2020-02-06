<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\DeepCopy\Matcher;

use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\Matcher\Matcher;
use DeepCopy\Reflection\ReflectionHelper;

final class StorableEntityMatcher implements Matcher
{
    /**
     * {@inheritdoc}
     */
    public function matches($object, $property): bool
    {
        if (!\is_object($object)) {
            return false;
        }

        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $storableEntity = $reflectionProperty->getValue($object);

        return ($storableEntity instanceof StorableEntity);
    }
}
