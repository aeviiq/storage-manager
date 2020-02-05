<?php declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Filter;

use Aeviiq\StorageManager\Exception\LogicException;
use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\Reflection\ReflectionHelper;
use DeepCopy\TypeFilter\TypeFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;

final class DoctrineEntityTypeFilter implements TypeFilter
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
     * {@inheritDoc}
     */
    public function apply($element)
    {
        $class = ($element instanceof Proxy) ? \get_parent_class($element) : \get_class($element);
        $metadata = $this->objectManager->getClassMetadata($class);
        $entityIdentifiers = $metadata->getIdentifier();

        $identifiers = [];
        $identifierPresent = false;
        foreach ($entityIdentifiers as $entityIdentifier) {
            $identifier = ReflectionHelper::getProperty($element, $entityIdentifier);
            $identifier->setAccessible(true);
            $value = $identifier->getValue($element);
            if (null !== $value) {
                $identifierPresent = true;
            }

            $identifiers[$entityIdentifier] = $value;
        }

        if (!$this->objectManager->contains($element) || !$identifierPresent) {
            throw LogicException::entityMustBePersistedAndFlushed($class);
        }

        return new StorableEntity($class, $identifiers);
    }
}
