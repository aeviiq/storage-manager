<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Store;

use Symfony\Component\HttpFoundation\RequestStack;

final class RequestStore implements StoreInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $masterKey;

    public function __construct(RequestStack $requestStack, string $masterKey = 'storage.manager.session.master.key')
    {
        $this->requestStack = $requestStack;
        $this->masterKey = $masterKey;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return $this->getSessionStore()->keys();
    }

    public function get(string $key): object
    {
        return $this->getSessionStore()->get($key);
    }

    public function has(string $key): bool
    {
        return $this->getSessionStore()->has($key);
    }

    public function set(string $key, object $data): void
    {
        $this->getSessionStore()->set($key, $data);
    }

    public function remove(string $key): void
    {
        $this->getSessionStore()->remove($key);
    }

    public function clear(): void
    {
        $this->getSessionStore()->clear();
    }

    private function getSessionStore(): StoreInterface
    {
        return new SessionStore($this->requestStack->getSession(), $this->masterKey);
    }
}
