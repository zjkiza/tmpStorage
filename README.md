# TMP Storage Bundle

The TMP Storage Bundle is used to store temporary state between two stateless requests. Common use cases include:

- **SSO Login**: During single sign-on, user data can be temporarily stored as an object in TMP Storage. A generated ID links the data, ensuring sensitive user information is not exposed in the URL. The ID is verified upon login, and the stored object is retrieved to complete the process.
- **Password Reset**: User data can be saved as an object in TMP Storage, and a reset link is generated with a unique ID. When the user clicks the link, they are directed to a page where the ID is verified, and the stored object is retrieved to facilitate password reset.
- **Unsubscribe Links**: User information is stored temporarily in TMP Storage, and an unsubscribe link is generated with a unique ID. When the user clicks the link, they are directed to the unsubscribe page, where the ID is verified, and the object is retrieved to process the request.

This bundle allows you to securely store objects in a temporary storage database, retrievable using randomly generated keys.

---

# About the Bundle

The bundle defines the interface `Zjk\TmpStorage\Contract\TmpStorageInterface` with the following methods:

- **`public function storage(object $tmp, int $ttl = 604800): string`**  
  Stores an object in the database. The `$ttl` parameter specifies the object's time-to-live in seconds (default: one week).

- **`public function fetch(string $id, bool $remove = true): object`**  
  Retrieves an object from the database by its ID. The `$remove` parameter determines whether the object is deleted immediately after retrieval. Set `$remove` to `false` to keep the object.

- **`public function remove(string $id): void`**  
  Removes an object from the database if `$remove` was previously set to `false`.

- **`public function clearGarbage(): void`**  
  Performs maintenance to remove invalid or expired records, ensuring database cleanliness.

### Maintenance Command

A terminal command is available for manual or scheduled garbage collection. It invokes the `clearGarbage` method:

```bash
bin/console zjkiza:tmp-storage:maintenance
```

# Installation

Add the bundle to your project using Composer:

```bash
composer require zjkiza/tmp-storage-bundle
```

## Symfony integration

The bundle integrates seamlessly with Symfony, wiring up all necessary classes. Follow these steps to set it up:

1. **Register the Bundle**

```php
<?php

declare(strict_types=1);

return [
    // other bundles
    Zjk\TmpStorage\ZJKizaTmpStorageBundle::class => ['all' => true],
];

```

2. **Configure the Bundle**

By default, the bundle uses a database table named `zjkiza_tmp_storag`e and the `doctrine.dbal.default_connection`. To customize these settings, create a `zjkiza_tmp_storage.yaml` configuration file in the config/packages directory with the following parameters:

```yaml
zjkiza_tmp_storage:
  dbal:
    table_name: your_table_name
    connection: ~

```
Replace your_table_name with your desired table name and configure the database connection as needed.


3. **Generating the Required Table**

To create the necessary table in the database, run the following command in your terminal:

```bash
bin/console doctrine:schema:update
```
This command instructs Doctrine to generate the table based on the bundle's configuration.




# Working with the bundle

```php

use Zjk\TmpStorage\Contract\TmpStorageInterface

class MyService {
    private TmpStorageInterface $storage;
    
    public function __construct(TmpStorageInterface $storage) 
    {
        $this->storage = $storage;
    }
    
    public function register(): string
    {
        $dto = new RegisterDto('test@text.com', 'lorem123');

        return $this->storage->storage($dto);
    }
    
    public function login(string $id): void
    {
        $dto = $this->storage->fetch($id);
        ......
    }
}
```

