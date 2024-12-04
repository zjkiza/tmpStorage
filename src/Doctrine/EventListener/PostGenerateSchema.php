<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Doctrine\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class PostGenerateSchema
{
    public const DEFAULT_TABLE_NAME = 'zjkiza_tmp_storage';

    private Connection $connection;

    private string $tableName;

    public function __construct(Connection $connection, string $tableName = self::DEFAULT_TABLE_NAME)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $connection = $eventArgs->getEntityManager()->getConnection();

        if ($connection !== $this->connection) {
            return;
        }

        $schema = $eventArgs->getSchema();
        $table = $schema->createTable($this->tableName);

        $table->addColumn('id', Types::STRING, ['length' => 36]);
        $table->addColumn('tmp_object', Types::TEXT);
        $table->addColumn('expires_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);

        $table->setPrimaryKey(['id']);

        if ($connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $table->addOption('engine', 'MyISAM');
        }
    }
}
