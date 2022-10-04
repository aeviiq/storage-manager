<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use DeepCopy\TypeMatcher\TypeMatcher;

final class EntityAttachMatcher extends TypeMatcher
{
    public function __construct()
    {
        parent::__construct('');
    }

    /**
     * {@inheritDoc}
     */
    public function matches($element): bool
    {
        // @phpstan-ignore-next-line
        return \is_object($element) && isset($element->{EntityDetachFilter::DETACHED_PROPERTY_ID});
    }
}
