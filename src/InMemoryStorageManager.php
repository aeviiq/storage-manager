<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use DeepCopy\DeepCopy;

final class InMemoryStorageManager implements StorageManager
{
    /**
     * @var object[]
     */
    private $memory = [];

    /**
     * @var DeepCopy
     */
    private $deepCopy;

    public function __construct(DeepCopy $deepCopy)
    {
        $this->deepCopy = $deepCopy;
    }

    public function save(string $key, object $data): void
    {
        $snapshot = $this->deepCopy->copy($data);

        // Ensure a snapshot is saved to prevent changes by reference without an explicit save() call.
        $this->memory[$key] = $snapshot;
    }

    public function load(string $key): object
    {
        if (!isset($this->memory[$key])) {
            throw InvalidArgumentException::dataKeyDoesNotExist($this, $key);
        }

        // Ensure a snapshot is loaded to prevent changes by reference without an explicit save() call.
        return $this->deepCopy->copy($this->memory[$key]);
    }

    public function has(string $key): bool
    {
        return isset($this->memory[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->memory[$key]);
    }

    public function clear(): void
    {
        $this->memory = [];
    }
}
