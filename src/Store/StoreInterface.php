<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager\Store;

interface StoreInterface
{
    /**
     * @return list<string>
     */
    public function keys(): array;

    public function get(string $key): object;

    public function has(string $key): bool;

    public function set(string $key, object $data): void;

    public function remove(string $key): void;

    public function clear(): void;
}
