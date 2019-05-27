<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use DeepCopy\DeepCopy;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStorageManager implements StorageManager
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string The key used to keep track of all data keys this manager manages.
     */
    private $masterKey;

    /**
     * @var DeepCopy
     */
    private $deepCopy;

    public function __construct(
        DeepCopy $deepCopy,
        SessionInterface $session,
        string $masterKey = 'storage.manager.session.master.key'
    ) {
        $this->session = $session;
        $this->masterKey = $masterKey;
        $this->deepCopy = $deepCopy;
    }

    public function save(string $key, object $data): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::saveKeySameAsMasterKey($this, $key);
        }

        // Ensure a snapshot is saved to prevent changes by reference without an explicit save() call.
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

        $data = $this->session->get($key);
        if (!is_object($data)) {
            // In this case the session data was overriden.
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
        $this->session->remove($key);
    }

    public function clear(): void
    {
        foreach ($this->session->get($this->masterKey, []) as $key) {
            $this->remove($key);
        }
    }
}
