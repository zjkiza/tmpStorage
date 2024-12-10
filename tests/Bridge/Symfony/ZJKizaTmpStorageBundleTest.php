<?php

declare(strict_types=1);

namespace Bridge\Symfony;

use PHPUnit\Framework\TestCase;
use Zjk\TmpStorage\Bridge\Symfony\DependencyInjection\Extension;
use Zjk\TmpStorage\Bridge\Symfony\ZJKizaTmpStorageBundle;

final class ZJKizaTmpStorageBundleTest extends TestCase
{
    public function testGetExtension(): void
    {
        $this->assertInstanceOf(Extension::class, (new ZJKizaTmpStorageBundle())->getContainerExtension());
    }
}
