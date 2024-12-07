<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Bridge\Symfony;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zjk\TmpStorage\Bridge\Symfony\DependencyInjection\Extension;

class ZJKizaTmpStorageBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }
}
