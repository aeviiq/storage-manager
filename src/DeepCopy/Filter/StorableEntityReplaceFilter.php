<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\DeepCopy\Filter;

use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Persistence\ObjectManager;

final class StorableEntityReplaceFilter implements Filter
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
    public function apply($object, $property, $objectCopier): void
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        /** @var StorableEntity $storableEntity */
        $storableEntity = $reflectionProperty->getValue($object);
        $repository = $this->objectManager->getRepository($storableEntity->getClass());
        $results = $repository->findBy($storableEntity->getIdentifiers());
        if (empty($results) || false === $entity = \reset($results)) {
            // Could occur when the entity is physically removed from the database between the save() and load().
            throw UnexpectedValueException::unableToLoadStorableEntity($storableEntity->getClass(), $storableEntity->getIdentifiers());
        }

        $reflectionProperty->setValue($object, $entity);
    }
}
