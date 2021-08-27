<?php declare(strict_types=1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\DeepCopy\Filter\DoctrineEntityTypeFilter;
use Aeviiq\StorageManager\DeepCopy\Filter\StorableEntityTypeFilter;
use Aeviiq\StorageManager\DeepCopy\Matcher\DoctrineEntityTypeMatcher;
use Aeviiq\StorageManager\DeepCopy\Matcher\StorableEntityTypeMatcher;
use Aeviiq\StorageManager\Store\StoreInterface;
use DeepCopy\DeepCopy;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Proxy;

final class StorageManager implements StorageManagerInterface
{
    /**
     * @var DeepCopy
     */
    private $deepCopy;

    /**
     * @var StoreInterface
     */
    private $storage;

    public function __construct(
        DeepCopy $deepCopy,
        StoreInterface $storage,
        ?ObjectManager $objectManager = null
    ) {
        $this->deepCopy = $deepCopy;
        $this->storage = $storage;

        if (null !== $objectManager) {
            $this->deepCopy->addTypeFilter(new DoctrineEntityTypeFilter($objectManager), new DoctrineEntityTypeMatcher(Proxy::class, $objectManager));
            $this->deepCopy->addTypeFilter(new StorableEntityTypeFilter($objectManager), new StorableEntityTypeMatcher());
        }
    }

    public function save(string $key, object $data): void
    {
        // Ensure a snapshot is saved to prevent changes by reference without an explicit save() call.
        $snapshot = $this->deepCopy->copy($data);
        $this->storage->set($key, $snapshot);
    }

    public function load(string $key): object
    {
        $data = $this->storage->get($key);

        return $this->deepCopy->copy($data);
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
