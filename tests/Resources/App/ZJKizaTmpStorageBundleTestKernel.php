<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Tests\Resources\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Ramsey\Uuid\Doctrine\UuidType;
use Zjk\TmpStorage\ZJKizaTmpStorageBundle;

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
        ]);
    }
}
