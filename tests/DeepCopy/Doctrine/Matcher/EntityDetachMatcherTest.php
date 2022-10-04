<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager\DeepCopy\Doctrine\Matcher;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityDetachMatcher;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;

final class EntityDetachMatcherTest extends TestCase
{
    /**
     * @psalm-suppress UndefinedPropertyAssignment
     */
    public function testMatches(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $matcher = new EntityDetachMatcher($objectManagerMock);
        self::assertFalse($matcher->matches('foo'));
        $proxyElement = new class() implements Proxy {
            public function __load(): void
            {
            }

            public function __isInitialized(): bool
            {
                return false;
            }
        };
        self::assertTrue($matcher->matches($proxyElement));

        // @phpstan-ignore-next-line
        $proxyElement->{EntityDetachFilter::DETACHED_PROPERTY_ID} = [];
        self::assertFalse($matcher->matches($proxyElement));

        $element = new class() {
        };
        $metadataFactoryMock = $this->createMock(ClassMetadataFactory::class);
        $metadataFactoryMock->expects(self::once())->method('isTransient')->with(\get_class($element))->willReturn(false);
        $objectManagerMock->expects(self::once())->method('getMetadataFactory')->willReturn($metadataFactoryMock);
        self::assertTrue($matcher->matches($element));
    }
}
