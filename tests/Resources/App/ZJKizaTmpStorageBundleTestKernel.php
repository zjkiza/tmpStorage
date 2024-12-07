<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Resources\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Zjk\TmpStorage\Bridge\Symfony\ZJKizaTmpStorageBundle;

class ZJKizaTmpStorageBundleTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return \realpath(__DIR__.'/..'); // @phpstan-ignore-line
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ZJKizaTmpStorageBundle(),
        ];
    }

    public function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret'        => 'test',
            'test'          => true,
            'property_info' => [
                'enabled' => true,
            ],
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_mysql',
                'url'    => 'mysql://developer:developer@mysql_bundle_3/developer',
                'use_savepoints' => true,
                'types' => [
                    'uuid' => UuidType::class,
                ],
            ],
            'orm'  => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy'             => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping'                => true,
                'enable_lazy_ghost_objects'   => true,
                'report_fields_where_declared' => true,
            ],
        ]);
    }
}
