<?php declare(strict_types=1);

namespace Aeviiq\StorageManager;

use Aeviiq\StorageManager\Exception\InvalidArgumentException;
use Aeviiq\StorageManager\Exception\UnexpectedValueException;

interface StorageManagerInterface
{
    /**
     * Save a deep copy of the data. If an object manager is injected, any entities that are present,
     * will be detached from the UnitOfWork. Their identifiers and values will be stored instead.
     * Upon loading, these will be used to retrieve the actual entities.
     * This ofcourse means that these objects are read-only, as the entity management
     * should be done by the entity manager.
     *
     * @param string $key  The key under which the data will be stored.
     * @param object $data The data being stored.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function save(string $key, object $data): void;

    /**
     * Returns a previous stored snapshot of the data. This will be a deep copy of the stored data,
     * to prevent referential changes without an explicit save() call. In case the saved data had
     * entities present, these will be automatically loaded and set back to the property they belonged
     * to before the save() occured.
     *
     * @param string $key The key under which the data is stored.
     *
     * @throws InvalidArgumentException If the key is invalid.
     * @throws UnexpectedValueException If the stored data is not of the expected value.
     */
    public function load(string $key): object;

    /**
     * Checks whether the given key exists.
     */
    public function has(string $key): bool;

    /**
     * Removes the saved data with the given key.
     *
     * @throws InvalidArgumentException If the key is invalid..
     */
    public function remove(string $key): void;

    /**
     * Removes all data that this manager stored, using the master key.
     */
    public function clear(): void;
}
