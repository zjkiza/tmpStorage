<?php

declare(strict_types=1);

namespace Doctrine\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use PHPUnit\Framework\TestCase;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;

final class PostGenerateSchemaTest extends TestCase
{
    public function testDifferentConnection(): void
    {
        $connection = $this->createMock(Connection::class);

        $entityManager  = $this->createMock(EntityManagerInterface::class);
        $schema  = $this->createMock(Schema::class);


        $generateSchemaEventArgs = new GenerateSchemaEventArgs($entityManager, $schema);

        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class));

        $schema->expects($this->never())->method('createTable');

        $postGenerateSchema = new PostGenerateSchema($connection);

        $postGenerateSchema->postGenerateSchema($generateSchemaEventArgs);
    }
}
