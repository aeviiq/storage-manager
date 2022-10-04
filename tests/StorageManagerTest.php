<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityAttachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityAttachMatcher;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityDetachMatcher;
use Aeviiq\StorageManager\StorageManager;
use Aeviiq\StorageManager\StorageManagerInterface;
use Aeviiq\StorageManager\Store\StoreInterface;
use DeepCopy\DeepCopy;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class StorageManagerTest extends TestCase
{
    private MockObject $storeMock;

    private MockObject $deepCopyMock;

    private StorageManagerInterface $storageManager;

    public function testConstruct(): void
    {
        $objectManagerStub = $this->createStub(ObjectManager::class);
        $deepCopyMock = $this->createMock(DeepCopy::class);
        $deepCopyMock->expects(self::exactly(2))->method('addTypeFilter')->withConsecutive(
            [new EntityDetachFilter($objectManagerStub), new EntityDetachMatcher($objectManagerStub)],
            [new EntityAttachFilter($objectManagerStub), new EntityAttachMatcher()]
        );
        (new StorageManager($deepCopyMock, $this->createStub(StoreInterface::class), $objectManagerStub));
    }

    public function testSave(): void
    {
        $class = new class() {
        };
        $clonedClass = new class() {
        };
        $this->deepCopyMock->expects(self::once())->method('copy')->with($class)->willReturn($clonedClass);
        $this->storeMock->expects(self::once())->method('set')->with('foo', $clonedClass);
        $this->storageManager->save('foo', $class);

    }

    public function testLoad(): void
    {
        $class = new class() {
        };
        $clonedClass = new class() {
        };
        $this->storeMock->expects(self::once())->method('get')->with('foo')->willReturn($class);
        $this->deepCopyMock->expects(self::once())->method('copy')->with($class)->willReturn($clonedClass);

        self::assertEquals($clonedClass, $this->storageManager->load('foo'));
    }

    public function testHas(): void
    {
        $this->storeMock->expects(self::exactly(2))->method('has')->withConsecutive(['foo'], ['bar'])->willReturnOnConsecutiveCalls(true, false);
        self::assertTrue($this->storageManager->has('foo'));
        self::assertFalse($this->storageManager->has('bar'));
    }

    public function testRemove(): void
    {
        $this->storeMock->expects(self::once())->method('remove')->with('foo');
        $this->storageManager->remove('foo');
    }

    public function testClear(): void
    {
        $this->storeMock->expects(self::once())->method('clear');
        $this->storageManager->clear();
    }

    /**
     * @required
     */
    protected function setUp(): void
    {
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->deepCopyMock = $this->createMock(DeepCopy::class);
        $this->storageManager = new StorageManager($this->deepCopyMock, $this->storeMock);
    }
}
