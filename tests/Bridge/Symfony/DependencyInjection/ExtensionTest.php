<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Bridge\Symfony\DependencyInjection;

use Doctrine\DBAL\Connection;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zjk\TmpStorage\Bridge\Symfony\DependencyInjection\Extension;
use Zjk\TmpStorage\Contract\TmpStorageInterface;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Doctrine\Repository\TmpStorageRepository;

final class ExtensionTest extends AbstractExtensionTestCase
{
    public function testDefaults(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('zjkiza_tmp_storage.dbal.table_name', 'zjkiza_tmp_storage');
        $this->assertContainerBuilderHasService(TmpStorageInterface::class, TmpStorageRepository::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TmpStorageRepository::class,
            0,
            new Reference('doctrine.dbal.default_connection', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TmpStorageRepository::class,
            1,
            '%zjkiza_tmp_storage.dbal.table_name%'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            PostGenerateSchema::class,
            0,
            new Reference('doctrine.dbal.default_connection', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            PostGenerateSchema::class,
            1,
            '%zjkiza_tmp_storage.dbal.table_name%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            PostGenerateSchema::class,
            'doctrine.event_listener',
            ['event' => 'postGenerateSchema']
        );
    }

    public function testConfigureDbal(): void
    {
        $this->container->setDefinition('testConnection', new Definition(Connection::class));

        $this->load([
            'dbal' => [
                'connection' => 'testConnection',
                'table_name' => 'testTable',
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(TmpStorageRepository::class, '$connection', new Reference('testConnection'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(PostGenerateSchema::class, '$connection', new Reference('testConnection'));
        $this->assertContainerBuilderHasParameter('zjkiza_tmp_storage.dbal.table_name', 'testTable');

    }

    protected function getContainerExtensions(): array
    {
        return [
            new Extension(),
        ];
    }
}
