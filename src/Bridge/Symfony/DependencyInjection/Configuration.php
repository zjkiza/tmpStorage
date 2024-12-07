<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zjk\TmpStorage\Doctrine\EventListener\PostGenerateSchema;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zjkiza_tmp_storage');
        $rootNode    = $treeBuilder->getRootNode();

        /**
         * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod, UndefinedMethod
         */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('dbal')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('table_name')
                            ->defaultValue(PostGenerateSchema::DEFAULT_TABLE_NAME)
                            ->info('You may change name of the table where tmp storage will be stored.')
                        ->end()
                        ->scalarNode('connection')
                            ->info('Name of the connection service which will be used. By default, default connection will be used.')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
