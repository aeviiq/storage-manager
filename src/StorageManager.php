<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

interface StorageManager
{
    /**
     * TODO description
     */
    public function save(string $key, object $data): void;

    /**
     * TODO description
     */
    public function load(string $key): object;

    /**
     * TODO description
     */
    public function has(string $key): bool;

    /**
     * TODO description
     */
    public function remove(string $key): void;

    /**
     * TODO description
     */
    public function clear(): void;
}
