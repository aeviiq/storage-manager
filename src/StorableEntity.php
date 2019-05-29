<?php declare(strict_types = 1);

namespace Aeviiq\StorageManager;

final class StorableEntity
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $identifiers;

    public function __construct(string $class, array $identifiers)
    {
        $this->class = $class;
        $this->identifiers = $identifiers;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }
}
