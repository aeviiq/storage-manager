<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Doctrine\Filter;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\LogicException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use DeepCopy\Reflection\ReflectionHelper;
use DeepCopy\TypeFilter\TypeFilter;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;

final class EntityDetachFilter implements TypeFilter
{
    public const DETACHED_PROPERTY_ID = 'identifiers_7c26f6ae97679422';

    public function __construct(private readonly ObjectManager $objectManager)
    {
    }

    /**
     * @param mixed $element
     *
     * @return object
     */
    public function apply($element)
    {
        if (!\is_object($element)) {
            throw InvalidArgumentException::invalidArgumentType('object', $element);
        }

        $class = ClassUtils::getRealClass(\get_class($element));
        $identifiers = $this->extractIdentifiers($this->objectManager->getClassMetadata($class), $element);

        if (!$this->objectManager->contains($element) || count(array_filter($identifiers)) === 0) {
            throw LogicException::entityMustBePersistedAndFlushed($class);
        }

        $copy = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        // @phpstan-ignore-next-line
        $copy->{self::DETACHED_PROPERTY_ID} = $identifiers;

        return $copy;
    }

    /**
     * @param ClassMetadata<object> $classMetadata
     *
     * @return array <string, string>
     */
    private function extractIdentifiers(ClassMetadata $classMetadata, object $element): array
    {
        $identifiers = [];
        foreach ($classMetadata->getIdentifier() as $entityIdentifier) {
            $identifier = ReflectionHelper::getProperty($element, $entityIdentifier);
            $value = $identifier->getValue($element);
            if (!\is_scalar($value)) {
                throw UnexpectedValueException::entityIdentifiersAreNotScalarType($value);
            }

            $identifiers[$entityIdentifier] = (string)$value;
        }

        return $identifiers;
    }
}
