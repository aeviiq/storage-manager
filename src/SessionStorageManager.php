<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use DeepCopy\DeepCopy;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStorageManager implements StorageManager
{
    /**
     * @var StorageManager
     */
    private $storage;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var DeepCopy
     */
    private $deepCopy;

    /**
     * @var string The key used to keep track of all data keys this manager manages.
     */
    private $masterKey;

    public function __construct(
        StorageManager $storage,
        SessionInterface $session,
        DeepCopy $deepCopy,
        string $masterKey = 'storage.manager.session.master.key'
    ) {
        $this->storage = $storage;
        $this->session = $session;
        $this->deepCopy = $deepCopy;
        $this->masterKey = $masterKey;
    }

    public function save(string $key, object $data): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::saveKeySameAsMasterKey($this, $key);
        }

        $this->storage->save($key, $data);
        $snapshot = $this->deepCopy->copy($data);
        $this->session->set($key, $snapshot);

        $keys = $this->session->get($this->masterKey, []);
        $keys[] = $key;
        $keys = array_unique($keys);

        $this->session->set($this->masterKey, $keys);
    }

    public function load(string $key): object
    {
        if (!$this->has($key)) {
            throw InvalidArgumentException::dataKeyDoesNotExist($this, $key);
        }

        if ($this->storage->has($key)) {
            return $this->storage->load($key);
        }

        $data = $this->session->get($key);
        if (!is_object($data)) {
            // In this case the session data was overriden.
            throw UnexpectedValueException::storageDataExpectedToBeObject($this, $key);
        }

        $snapshot = $this->deepCopy->copy($data);
        $this->storage->save($key, $snapshot);

        return $snapshot;
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function remove(string $key): void
    {
        $this->session->remove($key);
        $this->storage->remove($key);
    }

    public function clear(): void
    {
        foreach ($this->session->get($this->masterKey, []) as $key) {
            $this->session->remove($key);
        }

        $this->storage->clear();
    }
}
