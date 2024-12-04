<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Doctrine\Repository;

use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Exception\NotExistsException;
use Zjk\TmpStorage\Contract\TmpStorageInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

final class TmpStorageRepository implements TmpStorageInterface
{
    private Connection $connection;

    private string $tableName;

    public function __construct(Connection $connection, string $tableName = PostGenerateSchema::DEFAULT_TABLE_NAME)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function storage(object $tmp, int $ttl = 604800): string
    {
        $id = Uuid::uuid4()->toString();

        $expiredAt = new \DateTimeImmutable('now');
        $expiredAt = $expiredAt->add(new \DateInterval(\sprintf('PT%sS', $ttl)));

        $this->connection->insert($this->tableName, [
            'id' => $id,
            'tmp_object' => \serialize($tmp),
            'expires_at' => $expiredAt,
            'created_at' => new \DateTimeImmutable('now'),
        ], [
            'expires_at' => Types::DATETIME_IMMUTABLE,
            'created_at' => Types::DATETIME_IMMUTABLE,
        ]);

        return $id;
    }

    public function fetch(string $id, bool $remove = true): object
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', $this->tableName);

        /** @var array{id: string, tmp_object: string, expires_at: string, created_at: string}|false $row */
        $row = $this->connection->executeQuery($sql, [
            'id' => $id,
        ])->fetchAssociative();

        if (false === $row) {
            throw new NotExistsException(\sprintf('There is no data in the tmp storage with the key "%s"', $id));
        }

        $this->checkCreatedAndExpires($row, $id);

        $object = $this->unserializeSting($row['tmp_object']);

        if (true === $remove) {
            $this->remove($id);
        }

        return $object;
    }

    public function remove(string $id): void
    {
        $sql = \sprintf('DELETE FROM %s WHERE id = :id', $this->tableName);

        $this->connection->executeQuery($sql, [
            'id' => $id,
        ]);
    }

    public function clearGarbage(): void
    {
        $sql = \sprintf('DELETE FROM %s WHERE expires_at < now()', $this->tableName);

        $this->connection->executeQuery($sql);
    }

    /**
     * @param array{id: string, tmp_object: string, expires_at: string, created_at: string} $row
     */
    private function checkCreatedAndExpires(array $row, string $id): void
    {
        /** @var AbstractPlatform|null $dbPlatform */
        $dbPlatform = $this->connection->getDatabasePlatform();
        \assert(null !== $dbPlatform);

        $createdAt = $this->dateTimeImmutable($row['created_at'], $dbPlatform);
        $expiresAt = $this->dateTimeImmutable($row['expires_at'], $dbPlatform);

        if (false === $this->isTimeValid($createdAt, $expiresAt)) {
            $this->remove($id);
            throw new NotExistsException(\sprintf('There is no data in the tmp storage with the key "%s"', $id));
        }
    }

    private function isTimeValid(\DateTimeImmutable $createdAt, \DateTimeImmutable $expiresAt): bool
    {
        $dateTime = new \DateTime('now');

        if ($expiresAt < $dateTime) {
            return false;
        }

        if ($createdAt > $dateTime) {
            return false;
        }

        return true;
    }

    private function dateTimeImmutable(string $dateTime, AbstractPlatform $abstractPlatform): \DateTimeImmutable
    {
        /** @var \DateTimeImmutable $datetime */
        $datetime = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($dateTime, $abstractPlatform);

        return $datetime;
    }

    private function unserializeSting(string $serializedObject): object
    {
        try {
            /**
             * @noinspection UnserializeExploitsInspection
             *
             * @var object $object
             */
            $object = \unserialize($serializedObject);

            return $object;
        } finally {
            \restore_error_handler();
        }
    }
}
