<?php

declare(strict_types=1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityAttachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Filter\EntityDetachFilter;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityAttachMatcher;
use Aeviiq\StorageManager\DeepCopy\Doctrine\Matcher\EntityDetachMatcher;
use Aeviiq\StorageManager\Store\StoreInterface;
use DeepCopy\DeepCopy;
use Doctrine\Persistence\ObjectManager;

final class StorageManager implements StorageManagerInterface
{
    public function __construct(
        private readonly DeepCopy $deepCopy,
        private readonly StoreInterface $storage,
        private readonly ?ObjectManager $objectManager = null
    ) {
        if (null !== $this->objectManager) {
            $this->deepCopy->addTypeFilter(new EntityDetachFilter($this->objectManager), new EntityDetachMatcher($this->objectManager));
            $this->deepCopy->addTypeFilter(new EntityAttachFilter($this->objectManager), new EntityAttachMatcher());
        }
    }

    public function save(string $key, object $data): void
    {
        // Ensure a snapshot is saved to prevent changes by reference without an explicit save() call.
        $snapshot = $this->deepCopy->copy($data);
        \assert(\is_object($snapshot));
        $this->storage->set($key, $snapshot);
    }

    public function load(string $key): object
    {
        $data = $this->deepCopy->copy($this->storage->get($key));

        \assert(\is_object($data));

        return $data;
    }

    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }

    public function remove(string $key): void
    {
        $this->storage->remove($key);
    }

    public function clear(): void
    {
        $this->storage->clear();
    }
}
