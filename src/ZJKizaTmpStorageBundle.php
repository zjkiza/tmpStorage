<?php

declare(strict_types=1);

namespace Zjk\TmpStorage;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zjk\TmpStorage\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ZJKizaTmpStorageBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }
}
