<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionStorageManager implements StorageManager
{
    /**
     * @var StorageManager
     */
    private $memory;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string The key used to keep track of all data keys this manager manages.
     */
    private $masterKey;

    public function __construct(
        StorageManager $memory,
        SessionInterface $session,
        string $masterKey = 'storage.manager.session.master.key'
    ) {
        $this->memory = $memory;
        $this->session = $session;
        $this->masterKey = $masterKey;
    }

    public function save(string $key, object $data): void
    {
        if ($this->masterKey === $key) {
            throw InvalidArgumentException::saveKeySameAsMasterKey($this, $key);
        }

        $this->memory->save($key, $data);
        $this->session->set($key, $this->memory->load($key));

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

        if ($this->memory->has($key)) {
            return $this->memory->load($key);
        }

        $data = $this->session->get($key);
        if (!is_object($data)) {
            // In this case the session data was overriden.
            throw UnexpectedValueException::storageDataExpectedToBeObject($this, $key);
        }

        // Store the retrieved data in memory, so any further calls will retrieve it from there.
        $this->memory->save($key, $data);

        return $this->memory->load($key);
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function remove(string $key): void
    {
        $this->session->remove($key);
        $this->memory->remove($key);
    }

    public function clear(): void
    {
        foreach ($this->session->get($this->masterKey, []) as $key) {
            $this->session->remove($key);
        }

        $this->memory->clear();
    }
}
