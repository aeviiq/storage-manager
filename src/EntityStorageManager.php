<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use DeepCopy\DeepCopy;

final class EntityStorageManager implements StorageManager
{
    /**
     * @var StorageManager
     */
    private $storageManager;

    /**
     * @var object[]
     */
    private $memory = [];

    /**
     * @var DeepCopy
     */
    private $deepCopy;

    public function __construct(
        DeepCopy $deepCopy,
        StorageManager $storageManager
    ) {
        // TODO add entity manager interface
        $this->deepCopy = $deepCopy;
        $this->storageManager = $storageManager;
    }

    public function save(string $key, object $data): void
    {
        $this->memory[$key] = $data;
        $snapshot = $this->deepCopy->copy($data);
        // TODO detach the $snapshot

        $this->storageManager->save($key, $snapshot);
    }

    public function load(string $key): object
    {
        $snapshot = $this->storageManager->load($key);
        // TODO attach the $snapshot

        return $snapshot;
    }

    public function has(string $key): bool
    {
        return $this->storageManager->has($key);
    }

    public function remove(string $key): void
    {
        $this->storageManager->remove($key);
    }

    public function clear(): void
    {
        $this->storageManager->clear();
    }
}
