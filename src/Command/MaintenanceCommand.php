<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zjk\TmpStorage\Contract\TmpStorageInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'zjkiza:tmp-storage:maintenance',
    description: 'Clear expired records from tmp storage.'
)]
final class MaintenanceCommand extends Command
{
    public function __construct(private readonly TmpStorageInterface $tmpStorage)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->tmpStorage->clearGarbage();
            $io->success('All expired tmp storage are removed.');
        } catch (\Exception) {
            $io->error('Unable to clear expired tmp contents.');

            return self::FAILURE;
        }

        return Command::SUCCESS;
    }
}
