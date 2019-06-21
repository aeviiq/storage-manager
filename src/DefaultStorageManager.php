<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\DeepCopy\Filter\DoctrineEntityReplaceFilter;
use Aeviiq\StorageManager\DeepCopy\Filter\StorableEntityReplaceFilter;
use Aeviiq\StorageManager\DeepCopy\Matcher\DoctrineEntityMatcher;
use Aeviiq\StorageManager\DeepCopy\Matcher\StorableEntityMatcher;
use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use DeepCopy\DeepCopy;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class DefaultStorageManager implements StorageManager
{
    /**
     * @var DeepCopy
     */
    private $deepCopy;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string The key used to keep track of all data keys this manager manages.
     */
    private $masterKey;

    public function __construct(
        DeepCopy $deepCopy,
        SessionInterface $session,
        ?ObjectManager $objectManager = null,
        string $masterKey = 'storage.manager.session.master.key'
    ) {
        $this->deepCopy = $deepCopy;
        $this->session = $session;
        $this->masterKey = $masterKey;
        if (null !== $objectManager) {
            $this->deepCopy->addFilter(new DoctrineEntityReplaceFilter($objectManager), new DoctrineEntityMatcher($objectManager));
            $this->deepCopy->addFilter(new StorableEntityReplaceFilter($objectManager), new StorableEntityMatcher());
        }
    }

    public function save(string $key, object $data): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::saveKeySameAsMasterKey($this, $key);
        }

        // Ensure a snapshot is saved to prevent changes by reference without an explicit save() call.
        $snapshot = $this->deepCopy->copy($data);
        $this->session->set($key, $snapshot);
        $this->storeUsedKey($key);
    }

    public function load(string $key): object
    {
        if (!$this->has($key)) {
            throw InvalidArgumentException::dataKeyDoesNotExist($this, $key);
        }

        $data = $this->session->get($key);
        if (!\is_object($data)) {
            // Session data overriden by reference.
            throw UnexpectedValueException::storageDataExpectedToBeObject($this, $key);
        }

        return $this->deepCopy->copy($data);
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function remove(string $key): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::masterKeyCanNotBeRemoved($this, $key);
        }

        $this->session->remove($key);
    }

    public function clear(): void
    {
        foreach ($this->session->get($this->masterKey, []) as $key => $value) {
            $this->remove($key);
        }
    }

    private function storeUsedKey(string $key): void
    {
        $keys = $this->session->get($this->masterKey, []);
        $keys[$key] = true;

        $this->session->set($this->masterKey, $keys);
    }
}
