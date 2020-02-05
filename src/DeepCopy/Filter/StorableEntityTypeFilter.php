<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\DeepCopy\Filter;

use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\TypeFilter\TypeFilter;
use Doctrine\Common\Persistence\ObjectManager;

final class StorableEntityTypeFilter implements TypeFilter
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        /** @var StorableEntity $element */
        $repository = $this->objectManager->getRepository($element->getClass());
        $results = $repository->findBy($element->getIdentifiers());
        if (empty($results) || false === $entity = \reset($results)) {
            // Could occur when the entity is physically removed from the database between the save() and load().
            throw UnexpectedValueException::unableToLoadStorableEntity($element->getClass(), $element->getIdentifiers());
        }

        return $entity;
    }
}
