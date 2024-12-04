<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Repository;

use Doctrine\DBAL\Exception;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Exception\NotExistsException;
use Zjk\TmpStorage\Tests\Resources\KernelTestCase;
use Zjk\TmpStorage\Tests\Resources\Model\Foo;

final class TmpStorageRepositoryTest extends KernelTestCase
{
    /**
     * @throws Exception
     */
    public function testStore(): void
    {
        $connection    = $this->getContainer()->get('doctrine.dbal.default_connection');

        $repository = $this->getTmpStorage();

        $class = new Foo();

        $key = $repository->storage($class, 5000);

        $object = $repository->fetch($key);

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame($class->name, $object->name);

        $row = $connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertFalse($row);
    }

    /**
     * @throws Exception
     */
    public function testStoreAndReadingOfStorageWithoutRemovingRecords(): void
    {
        $connection    = $this->getContainer()->get('doctrine.dbal.default_connection');

        $repository = $this->getTmpStorage();

        $class = new Foo();

        $key = $repository->storage($class, 5000);

        $object = $repository->fetch($key, false);

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame($class->name, $object->name);

        $row = $connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertIsArray($row);
    }

    /**
     * @throws Exception
     */
    public function testRemoveKeyFromStorage(): void
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $repository = $this->getTmpStorage();

        $class = new Foo();

        $key = $repository->storage($class, 5000);

        $repository->remove($key);

        $row = $connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertFalse($row);
    }

    /**
     * @throws Exception
     */
    public function testClearGarbage(): void
    {
        $connection    = $this->getContainer()->get('doctrine.dbal.default_connection');

        $repository = $this->getTmpStorage();

        $class = new Foo();

        $key1 = $repository->storage($class, 60000);
        $key2 = $repository->storage($class, 1);

        $rows = $connection->executeQuery(\sprintf('SELECT * FROM %s ORDER BY created_at', PostGenerateSchema::DEFAULT_TABLE_NAME))->fetchAllAssociative();

        $this->assertCount(2, $rows);
        $this->assertSame($rows[0]['id'], $key1);
        $this->assertSame($rows[1]['id'], $key2);

        \sleep(2);
        $repository->clearGarbage();

        $rows = $connection->executeQuery(\sprintf('SELECT * FROM %s', PostGenerateSchema::DEFAULT_TABLE_NAME))->fetchAllAssociative();

        $this->assertCount(1, $rows);
        $this->assertSame($rows[0]['id'], $key1);
    }

    /**
     * @throws Exception
     */
    public function testExpectExceptionWhenDataNotExistInStorage(): void
    {
        $id = 'lorem-ipsum';
        $this->expectException(NotExistsException::class);
        $this->expectExceptionMessage(\sprintf('There is no data in the tmp storage with the key "%s"', $id));

        $repository = $this->getTmpStorage();

        $repository->fetch($id);
    }

    /**
     * @throws Exception
     */
    public function testExpectExceptionWhenTheTimeoutHasExpired(): void
    {
        $this->expectException(NotExistsException::class);

        $repository = $this->getTmpStorage();

        $class = new Foo();

        $key = $repository->storage($class, 1);

        \sleep(2);

        $repository->fetch($key);
    }
}
