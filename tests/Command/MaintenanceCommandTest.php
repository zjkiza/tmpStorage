<?php

declare(strict_types=1);

namespace Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Zjk\TmpStorage\Command\MaintenanceCommand;
use Zjk\TmpStorage\Contract\TmpStorageInterface;

final class MaintenanceCommandTest extends TestCase
{
    public function testSuccess(): void
    {
        /** @var TmpStorageInterface&MockObject $storage */
        $storage = $this->createMock(TmpStorageInterface::class);
        $storage->expects($this->once())->method('clearGarbage');

        $command = new MaintenanceCommand($storage);
        $output  = new BufferedOutput();
        $result  = $command->run(new ArrayInput([]), $output);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('[OK] All expired tmp storage are removed.', $output->fetch());
    }

    public function testFail(): void
    {
        /** @var TmpStorageInterface&MockObject $storage */
        $storage = $this->createMock(TmpStorageInterface::class);
        $storage->expects($this->once())->method('clearGarbage')->willThrowException(new \Exception('lorem'));

        $command = new MaintenanceCommand($storage);
        $output  = new BufferedOutput();
        $result  = $command->run(new ArrayInput([]), $output);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('[ERROR] Unable to clear expired tmp contents.', $output->fetch());
    }
}
