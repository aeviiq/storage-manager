<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager\DeepCopy\Filter;

use Aeviiq\StorageManager\Exception\LogicException;
use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;

final class DoctrineEntityReplaceFilter implements Filter
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
        $entity = $reflectionProperty->getValue($object);
        $class = ($entity instanceof Proxy) ? \get_parent_class($entity) : \get_class($entity);

        $metadata = $this->objectManager->getClassMetadata($class);
        $entityIdentifiers = $metadata->getIdentifier();

        $identifiers = [];
        $identifierPresent = false;
        foreach ($entityIdentifiers as $entityIdentifier) {
            $identifier = ReflectionHelper::getProperty($entity, $entityIdentifier);
            $identifier->setAccessible(true);
            $value = $identifier->getValue($entity);
            if (null !== $value) {
                $identifierPresent = true;
            }

            $identifiers[$entityIdentifier] = $value;
        }

        if (!$this->objectManager->contains($entity) || !$identifierPresent) {
            throw LogicException::entityMustBePersistedAndFlushed($class);
        }

        $reflectionProperty->setValue($object, new StorableEntity($class, $identifiers));
    }
}
