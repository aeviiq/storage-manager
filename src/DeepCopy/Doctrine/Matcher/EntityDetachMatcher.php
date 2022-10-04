<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use DeepCopy\TypeMatcher\TypeMatcher;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Proxy;

final class EntityDetachMatcher extends TypeMatcher
{
    public function __construct(private readonly ObjectManager $objectManager)
    {
        parent::__construct(Proxy::class);
    }

    /**
     * {@inheritDoc}
     */
    public function matches($element): bool
    {
        if (!\is_object($element)) {
            return false;
        }

        if (parent::matches($element) || !$this->objectManager->getMetadataFactory()->isTransient(\get_class($element))) {
            // @phpstan-ignore-next-line
            return !isset($element->{EntityDetachFilter::DETACHED_PROPERTY_ID});
        }

        return false;
    }
}
