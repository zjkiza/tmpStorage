<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;
use Zjk\TmpStorage\Doctrine\Repository\TmpStorageRepository;

class Extension extends SymfonyExtension
{
    public function getAlias(): string
    {
        return 'zjkiza_tmp_storage';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('zjkiza_tmp_storage.dbal.table_name', $config['dbal']['table_name']);

        $connection = &$config['dbal']['connection'];
        if (null !== $connection) {
            $container->findDefinition(TmpStorageRepository::class)->setArgument('$connection', new Reference($connection));
            $container->findDefinition(PostGenerateSchema::class)->setArgument('$connection', new Reference($connection));
        }
    }
}
