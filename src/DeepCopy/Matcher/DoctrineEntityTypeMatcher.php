<?php declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Matcher;

use DeepCopy\TypeMatcher\TypeMatcher;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;

final class DoctrineEntityTypeMatcher extends TypeMatcher
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($type, ObjectManager $objectManager)
    {
        parent::__construct($type);
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function matches($element)
    {
        if (parent::matches($element)) {
            return true;
        }

        if (!\is_object($element)) {
            return false;
        }

        $class = ($element instanceof Proxy) ? \get_parent_class($element) : \get_class($element);

        return !$this->objectManager->getMetadataFactory()->isTransient($class);
    }
}
