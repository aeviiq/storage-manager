# Storage Manager Component

## Why

To provide an easy way to store data with references to Doctrine entities, without storing the entities or their proxies themselves. A deep copy of the original object is made, in which
any entity will be detached and have their identifiers stored with them. These will be used to retrieve a managed entity
upon load().

The objects are saved as a copy, meaning referential changes will not affect the object that is stored. To persist any changes, save() the object. See example below.

#### Support for readonly properties

<em>To copy an object, the StorageManager uses the [DeepCopy](https://github.com/myclabs/DeepCopy) component from MyClabs. This component does not yet have support for the newly introducted [readonly properties](https://wiki.php.net/rfc/readonly_properties_v2) in PHP 8.1.</em>

<em>They do have an open [ticket](https://github.com/myclabs/DeepCopy/issues/174) to support this.</em>

## Installation
```
composer require aeviiq/storage-manager
```

## Usage

```php
final class Foo
{
    public function __construct(private readonly StorageManagerInterface $storageManager)
    {
    }
    
    public function __invoke(): void
    {
        $object = new stdClass();
        $object->foo = 'foo';
        
        $this->storageManager->save('some_key', $object);
        // These changes are made after the save() call and will not be there upon load().
        $object->foo = 'bar';
        
        $loadedObject = $this->storageManager->load('some_key');
        
        $object === $loadedObject; // false
        $loadedObject->foo === 'bar' // false
    }
}
```
