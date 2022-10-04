<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Doctrine\Filter;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\LogicException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use DeepCopy\TypeFilter\TypeFilter;
use Doctrine\Persistence\ObjectManager;

final class EntityAttachFilter implements TypeFilter
{
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

        // @phpstan-ignore-next-line
        if (!isset($element->{EntityDetachFilter::DETACHED_PROPERTY_ID})) {
            throw LogicException::entityPropertyIdIsMissing();
        }

        /** @var array<string, mixed> $identifiers */
        // @phpstan-ignore-next-line
        $identifiers = $element->{EntityDetachFilter::DETACHED_PROPERTY_ID};
        $repository = $this->objectManager->getRepository(\get_class($element));
        $results = $repository->findBy($identifiers);
        $entity = \reset($results);
        if (0 === count($results) || false === $entity) {
            // Could occur when the entity is removed from the database between the save() and load().
            throw UnexpectedValueException::unableToLoadStorableEntity(\get_class($element), $identifiers);
        }

        return $entity;
    }
}
