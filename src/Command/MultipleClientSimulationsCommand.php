<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:simulate-multi-client',
    description: 'Run multiple client simulations concurrently'
)]
class MultipleClientSimulationsCommand extends Command
{
    protected const CLIENTS_COUNT = 3;
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting multiple client simulations...');

        $processes = [];

        for ($i = 0; $i < self::CLIENTS_COUNT; $i++) {
            $process = new Process(['php', 'bin/console', SimulateSingleClientCommand::NAME]);
            $process->start();

            $processes[] = $process;
        }

        foreach ($processes as $process) {
            while ($process->isRunning()) {
                // waiting for process to finish
            }
            $output->writeln($process->getOutput());
        }

        $output->writeln('All client simulations have ended.');

        return Command::SUCCESS;
    }
}
