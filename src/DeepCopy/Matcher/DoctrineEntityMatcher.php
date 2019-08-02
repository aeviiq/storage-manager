<?php declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Matcher;

use DeepCopy\Matcher\Matcher;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;

final class DoctrineEntityMatcher implements Matcher
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
    public function matches($object, $property): bool
    {
        if (!\is_object($object)) {
            return false;
        }

        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $entity = $reflectionProperty->getValue($object);
        if (!\is_object($entity)) {
            return false;
        }

        $class = ($entity instanceof Proxy) ? \get_parent_class($entity) : \get_class($entity);

        return !$this->objectManager->getMetadataFactory()->isTransient($class);
    }
}
