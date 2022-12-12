<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager\DeepCopy\Doctrine\Filter;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\LogicException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EntityDetachFilterTest extends TestCase
{
    private MockObject $objectManagerMock;

    private EntityDetachFilter $filter;

    public function testApply(): void
    {
        $element = new class() {
            // @phpstan-ignore-next-line
            private int $id = 1;
        };

        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $this->objectManagerMock->expects(self::once())->method('getClassMetadata')->with(\get_class($element))->willReturn($classMetadataMock);
        $classMetadataMock->expects(self::once())->method('getIdentifier')->willReturn([0 => 'id']);
        $this->objectManagerMock->expects(self::once())->method('contains')->with($element)->willReturn(true);

        $result = $this->filter->apply($element);
        // @phpstan-ignore-next-line
        self::assertTrue(isset($result->{EntityDetachFilter::DETACHED_PROPERTY_ID}));
        // @phpstan-ignore-next-line
        self::assertEquals(['id' => '1'], $result->{EntityDetachFilter::DETACHED_PROPERTY_ID});
        self::assertInstanceOf($element::class, $result);
        self::assertNotEquals($result, $element);
    }

    public function testApplyWillThrowExceptionWhenElementIsNotAnObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected type "object", "array" given.');
        $this->filter->apply([]);
    }

    public function testApplyWillThrowExceptionWhenEntityIsNotYetPersistedAndFlushed(): void
    {
        $element = new class() {
            // @phpstan-ignore-next-line
            private int $id = 1;
        };
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $this->objectManagerMock->expects(self::once())->method('getClassMetadata')->with(\get_class($element))->willReturn($classMetadataMock);
        $classMetadataMock->expects(self::once())->method('getIdentifier')->willReturn([0 => 'id']);
        $this->objectManagerMock->expects(self::once())->method('contains')->with($element)->willReturn(false);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(\sprintf('The entity "%s" must be persisted and flushed in order to be detached.', \get_class($element)));
        $this->filter->apply($element);
    }

    public function testApplyWillThrowExceptionWhenEntityHasNoIndentifiers(): void
    {
        $element = new class() {
        };
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $this->objectManagerMock->expects(self::once())->method('getClassMetadata')->with(\get_class($element))->willReturn($classMetadataMock);
        $classMetadataMock->expects(self::once())->method('getIdentifier')->willReturn([]);
        $this->objectManagerMock->expects(self::once())->method('contains')->with($element)->willReturn(true);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(\sprintf('The entity "%s" must be persisted and flushed in order to be detached.', \get_class($element)));
        $this->filter->apply($element);
    }

    public function testApplySupportsCompositeKeys(): void
    {
        $compositeEntity = new class() {
            // @phpstan-ignore-next-line
            private int $id = 2;
        };
        $element = new class($compositeEntity) {
            // @phpstan-ignore-next-line
            private int $foo = 1;

            // @phpstan-ignore-next-line
            private object $compositeEntity;

            public function __construct(object $compositeEntity)
            {
                $this->compositeEntity = $compositeEntity;
            }
        };

        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $classMetadataFactoryMock = $this->createMock(ClassMetadataFactory::class);
        $classMetadataFactoryMock->expects(self::once())->method('isTransient')->with(\get_class($compositeEntity))->willReturn(false);
        $this->objectManagerMock
            ->expects(self::exactly(2))
            ->method('getClassMetadata')
            ->withConsecutive([\get_class($element)], [\get_class($compositeEntity)])
            ->willReturnOnConsecutiveCalls($classMetadataMock, $classMetadataMock);
        $this->objectManagerMock->expects(self::once())->method('getMetadataFactory')->willReturn($classMetadataFactoryMock);
        $classMetadataMock->expects(self::exactly(2))->method('getIdentifier')->willReturnOnConsecutiveCalls([0 => 'foo', 1 => 'compositeEntity'], [0 => 'id']);
        $this->objectManagerMock->expects(self::once())->method('contains')->with($element)->willReturn(true);

        $result = $this->filter->apply($element);
        // @phpstan-ignore-next-line
        self::assertTrue(isset($result->{EntityDetachFilter::DETACHED_PROPERTY_ID}));
        // @phpstan-ignore-next-line
        self::assertEquals(['foo' => '1', 'compositeEntity' => ['id' => '2']], $result->{EntityDetachFilter::DETACHED_PROPERTY_ID});
        self::assertInstanceOf($element::class, $result);
        self::assertNotEquals($result, $element);
    }

    /**
     * @required
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->filter = new EntityDetachFilter($this->objectManagerMock);
    }
}
