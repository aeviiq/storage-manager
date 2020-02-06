<?php declare(strict_types=1);

namespace Aeviiq\StorageManager\DeepCopy\Matcher;

use Aeviiq\StorageManager\StorableEntity;
use DeepCopy\TypeMatcher\TypeMatcher;

final class StorableEntityTypeMatcher extends TypeMatcher
{
    public function __construct()
    {
        parent::__construct(StorableEntity::class);
    }
}
