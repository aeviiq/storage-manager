<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Store;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStore implements StoreInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $masterKey;

    public function __construct(SessionInterface $session, string $masterKey = 'storage.manager.session.master.key')
    {
        $this->session = $session;
        $this->masterKey = $masterKey;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return $this->session->get($this->masterKey, []);
    }

    public function get(string $key): object
    {
        $item = $this->session->get($this->masterKey);
        if (null === $item) {
            InvalidArgumentException::dataKeyDoesNotExist($this, $key);
        }

        if (!is_object($item)) {
            throw UnexpectedValueException::storageDataExpectedToBeObject($this, $key);
        }

        return $item;
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function set(string $key, object $data): void
    {
        $this->session->set($key, $data);

        $keys = $this->keys();
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            $this->session->set($this->masterKey, $keys);
        }
    }

    public function remove(string $key): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::masterKeyCanNotBeRemoved($this, $key);
        }

        $this->session->remove($key);

        $keys = $this->keys();
        $index = array_search($key, $keys);
        if (null !== $index) {
            unset($keys[$key]);
            $this->session->set($this->masterKey, $keys);
        }
    }

    public function clear(): void
    {
        foreach ($this->keys() as $key) {
            $this->remove($key);
        }
    }
}
