<?php

declare(strict_types=1);

namespace Aeviiq\Tests\StorageManager\Store;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Aeviiq\StorageManager\Store\SessionStore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStoreTest extends TestCase
{
    private const MASTER_KEY = 'master_key';

    private MockObject $sessionMock;

    private SessionStore $store;

    public function testKeys(): void
    {
        $this->sessionMock->expects(self::once())->method('get')->with(self::MASTER_KEY)->willReturn(['foo', 'bar']);
        self::assertEquals(['foo', 'bar'], $this->store->keys());

    }

    public function testGet(): void
    {
        $class = new class() {
        };
        $this->sessionMock->expects(self::once())->method('get')->with('foo')->willReturn($class);
        self::assertEquals($class, $this->store->get('foo'));
    }

    public function testGetWillThrowExceptionWhenItemCouldNotBeFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Data with key "foo" does not exist.'));
        $this->sessionMock->expects(self::once())->method('get')->willReturn(null);
        $this->store->get('foo');
    }

    public function testGetWillThrowExceptionWhenItemIsNotAnObject(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Data with key "foo" must be an object.');
        $this->sessionMock->expects(self::once())->method('get')->willReturn('bar');
        $this->store->get('foo');

    }

    public function testHas(): void
    {
        $this->sessionMock->expects(self::exactly(2))->method('has')->withConsecutive(['foo'], ['bar'])->willReturnOnConsecutiveCalls(true, false);
        self::assertTrue($this->store->has('foo'));
        self::assertFalse($this->store->has('bar'));
    }

    public function testSet(): void
    {
        $class = new class() {
        };
        $this->sessionMock->expects(self::once())->method('get')->with(self::MASTER_KEY)->willReturn([]);
        $this->sessionMock->expects(self::exactly(2))->method('set')->withConsecutive(['foo', $class], [self::MASTER_KEY, ['foo']]);
        $this->store->set('foo', $class);
    }

    public function testSetWillThrowExceptionWhenKeyIsSameAsMasterKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The save key "%s" cannot be the same as the master key.', self::MASTER_KEY));

        $this->store->set(
            self::MASTER_KEY,
            new class() {
            }
        );
    }

    public function testRemove(): void
    {
        $this->sessionMock->expects(self::once())->method('remove')->with('foo');
        $this->sessionMock->expects(self::once())->method('get')->with(self::MASTER_KEY)->willReturn(['foo', 'bar']);
        $this->sessionMock->expects(self::once())->method('set')->with(self::MASTER_KEY, ['1' => 'bar']);
        $this->store->remove('foo');
    }

    public function testRemoveWillThrowExceptionWhenKeyIsSameAsMasterKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The master key "%s" cannot be used in a single remove.', self::MASTER_KEY));

        $this->store->remove(self::MASTER_KEY);
    }

    public function testClear(): void
    {
        $this->sessionMock->expects(self::once())->method('set')->with(self::MASTER_KEY, []);
        $this->store->clear();

    }

    /**
     * @required
     */
    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->sessionMock);
        $this->store = new SessionStore($requestStack, self::MASTER_KEY);
    }
}
