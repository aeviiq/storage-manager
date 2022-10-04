<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager\DeepCopy\Doctrine\Filter;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityAttachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\LogicException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EntityAttachFilterTest extends TestCase
{
    private MockObject $objectManagerMock;

    private EntityAttachFilter $filter;

    /**
     * @psalm-suppress UndefinedPropertyAssignment
     */
    public function testApply(): void
    {
        $element = new class() {
        };
        // @phpstan-ignore-next-line
        $element->{EntityDetachFilter::DETACHED_PROPERTY_ID} = [
            'id' => 1,
        ];
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $this->objectManagerMock->expects(self::once())->method('getRepository')->with(\get_class($element))->willReturn($repositoryMock);
        $entity = new class() {
        };
        $repositoryMock->expects(self::once())->method('findBy')->willReturn([$entity]);

        self::assertEquals($entity, $this->filter->apply($element));
    }

    public function testApplyWillThrowExceptionWhenElementIsNotAnObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected type "object", "array" given.');
        $this->filter->apply([]);
    }

    public function testApplyWillThrowExceptionWhenPropertyIdIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Required entity property id is missing. Did you use the correct Matcher?');
        $this->filter->apply(
            new class() {
            }
        );

    }

    /**
     * @psalm-suppress UndefinedPropertyAssignment
     */
    public function testApplyWillThrowExceptionWhenEntityCouldNotBeFound(): void
    {
        $element = new class() {
        };
        // @phpstan-ignore-next-line
        $element->{EntityDetachFilter::DETACHED_PROPERTY_ID} = [
            'id' => 1,
        ];
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $this->objectManagerMock->expects(self::once())->method('getRepository')->with(\get_class($element))->willReturn($repositoryMock);
        $repositoryMock->expects(self::once())->method('findBy')->willReturn([]);
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(\sprintf('The entity "%s" with identifier(s) "id" and value(s) "1" could not be found by the given entity manager. ', \get_class($element)));
        $this->filter->apply($element);
    }

    /**
     * @required
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->filter = new EntityAttachFilter($this->objectManagerMock);
    }
}
