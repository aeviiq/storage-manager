<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Store;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStore implements StoreInterface
{
    public function __construct(private readonly RequestStack $requestStack, private readonly string $masterKey = 'storage.manager.session.master.key')
    {
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): array
    {
        /** @var string[] $keys */
        $keys = $this->getSession()->get($this->masterKey, []);

        return $keys;
    }

    public function get(string $key): object
    {
        $item = $this->getSession()->get($key);
        if (null === $item) {
            throw InvalidArgumentException::dataKeyDoesNotExist($key);
        }

        if (!\is_object($item)) {
            throw UnexpectedValueException::storageDataExpectedToBeObject($key);
        }

        return $item;
    }

    public function has(string $key): bool
    {
        return $this->getSession()->has($key);
    }

    public function set(string $key, object $data): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::saveKeySameAsMasterKey($key);
        }

        $this->getSession()->set($key, $data);

        $keys = $this->keys();
        if (!\in_array($key, $keys, true)) {
            $keys[] = $key;
            $this->getSession()->set($this->masterKey, $keys);
        }
    }

    public function remove(string $key): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::masterKeyCanNotBeRemoved($key);
        }

        $this->getSession()->remove($key);

        $keys = $this->keys();
        $index = \array_search($key, $keys, true);
        if (false !== $index) {
            unset($keys[$index]);
            $this->getSession()->set($this->masterKey, $keys);
        }
    }

    public function clear(): void
    {
        $this->getSession()->set($this->masterKey, []);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
