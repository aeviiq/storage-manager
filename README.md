# Storage Manager Component

## Why

To provide an easy way to store data with references to proxies, without storing the proxies themselves. A deep copy of the original object is made, in which
any proxies will be turned into a StorableEntity, which contains only the proxy class name and it's identifiers. These will be used to retrieve a managed proxy
upon load().

## Installation
```
composer require aeviiq/storage-manager
```

## Declaration
##### With the Symfony service container
```yaml
// config/services.yml
DeepCopy\DeepCopy:
    shared: false

Aeviiq\StorageManager\StorageManagerInterface: '@Aeviiq\StorageManager\StorageManager'
Aeviiq\StorageManager\StoreInterface: '@Aeviiq\StorageManager\RequestStore'

Aeviiq\StorageManager\RequestStore:
    autowire: true

Aeviiq\StorageManager\StorageManager:
    autowire: true
```

## Usage
```php
final class FooController
{
    /**
     * @var StorageManagerInterface
     */
    private $storageManager;

    public function __construct(StorageManagerInterface $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function step1(Request $request, EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Foo::class);
        $foo = $repository->find(1);
        $bar = new Bar('some value', 1234, $foo);
        
        $this->storageManager->save('some_key', $bar);
        // A copy of $bar is saved and the entity is converted to a StorableEntity.
        // The actual entity/proxy itself does not get saved into the session.
        // $bar is untouched and can be used like normal.
        // Any changes done to $bar after the save() will not be stored without
        // and explicit save() call.

        return new Response('Some response.');
    }

    public function step2(Request $request): Response
    {
        // $bar will have all last saved values and $foo will be a managed entity.
        $bar = $this->storageManager->load('some_key');
    }
}
```
