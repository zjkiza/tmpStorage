<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Resources;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Zjk\TmpStorage\Contract\TmpStorageInterface;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Doctrine\Repository\TmpStorageRepository;
use Zjk\TmpStorage\Tests\Resources\App\ZJKizaTmpStorageBundleTestKernel;

class KernelTestCase extends SymfonyKernelTestCase
{
    protected static function getKernelClass(): string
    {
        return ZJKizaTmpStorageBundleTestKernel::class;
    }

    /**
     * @internal
     *
     * @throws Exception
     */
    public function getTmpStorage(): TmpStorageInterface
    {
        /** @var Connection $connection */
        $connection    = $this->getContainer()->get('doctrine.dbal.default_connection');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist([PostGenerateSchema::DEFAULT_TABLE_NAME])) {
            $schemaManager->dropTable(PostGenerateSchema::DEFAULT_TABLE_NAME);
        }

        $schema = $schemaManager->introspectSchema();

        $event     = new GenerateSchemaEventArgs($entityManager, $schema);
        $generator = new PostGenerateSchema($connection);

        $generator->postGenerateSchema($event);
        $connection->executeQuery($schema->toSql($connection->getDatabasePlatform())[0]);

        return new TmpStorageRepository($connection);
    }
}
