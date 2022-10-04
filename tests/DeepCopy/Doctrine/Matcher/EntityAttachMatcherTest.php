<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager\DeepCopy\Doctrine\Matcher;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityAttachMatcher;
use PHPUnit\Framework\TestCase;

final class EntityAttachMatcherTest extends TestCase
{
    /**
     * @psalm-suppress UndefinedPropertyAssignment
     */
    public function testMatches(): void
    {
        $element = new class() {
        };
        $matcher = new EntityAttachMatcher();
        self::assertFalse($matcher->matches($element));
        // @phpstan-ignore-next-line
        $element->{EntityDetachFilter::DETACHED_PROPERTY_ID} = [];
        self::assertTrue($matcher->matches($element));
    }
}
