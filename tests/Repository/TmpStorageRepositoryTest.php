<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Zjk\TmpStorage\Contract\TmpStorageInterface;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Exception\NotExistsException;
use Zjk\TmpStorage\Tests\Resources\KernelTestCase;
use Zjk\TmpStorage\Tests\Resources\Model\Foo;

final class TmpStorageRepositoryTest extends KernelTestCase
{
    private Connection $connection;
    private TmpStorageInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getConnection();

        $this->repository = $this->getTmpStorage($this->connection);
    }

    /**
     * @throws Exception
     */
    public function testStore(): void
    {
        $class = new Foo();

        $key = $this->repository->storage($class, 5000);

        $object = $this->repository->fetch($key);

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame($class->name, $object->name);

        $row = $this->connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertFalse($row);
    }

    /**
     * @throws Exception
     */
    public function testStoreAndReadingOfStorageWithoutRemovingRecords(): void
    {
        $class = new Foo();

        $key = $this->repository->storage($class, 5000);

        $object = $this->repository->fetch($key, false);

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame($class->name, $object->name);

        $row = $this->connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertIsArray($row);
    }

    /**
     * @throws Exception
     */
    public function testRemoveKeyFromStorage(): void
    {
        $class = new Foo();

        $key = $this->repository->storage($class, 5000);

        $this->repository->remove($key);

        $row = $this->connection->executeQuery(\sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', PostGenerateSchema::DEFAULT_TABLE_NAME), [
            'id' => $key,
        ])->fetchAssociative();

        $this->assertFalse($row);
    }

    /**
     * @throws Exception
     */
    public function testClearGarbage(): void
    {
        $class = new Foo();

        $key1 = $this->repository->storage($class, 60000);
        $key2 = $this->repository->storage($class, 1);

        $rows = $this->connection->executeQuery(\sprintf('SELECT * FROM %s ORDER BY created_at', PostGenerateSchema::DEFAULT_TABLE_NAME))->fetchAllAssociative();

        $this->assertCount(2, $rows);
        $this->assertSame($rows[0]['id'], $key1);
        $this->assertSame($rows[1]['id'], $key2);

        \sleep(2);
        $this->repository->clearGarbage();

        $rows = $this->connection->executeQuery(\sprintf('SELECT * FROM %s', PostGenerateSchema::DEFAULT_TABLE_NAME))->fetchAllAssociative();

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

        $this->repository->fetch($id);
    }

    /**
     * @throws Exception
     */
    public function testExpectExceptionWhenTheTimeoutHasExpired(): void
    {
        $this->expectException(NotExistsException::class);

        $class = new Foo();

        $key = $this->repository->storage($class, 1);

        \sleep(2);

        $this->repository->fetch($key);
    }

    private function getConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        return $connection;
    }
}
