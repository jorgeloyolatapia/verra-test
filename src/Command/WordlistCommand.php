<?php

declare(strict_types=1);

namespace App\Command;

use App\Handler\WordlistHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:wordlist',
    description: 'Prints the words of a list that are the concatenation of two other words of the same list.',
)]
final class WordlistCommand extends Command
{
    public function __construct(private readonly WordlistHandler $wordlistHandler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to the word list file (one word per line)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $lines = $this->wordlistHandler->handle((string) $input->getArgument('file'));
        } catch (\RuntimeException $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        foreach ($lines as $line) {
            $output->writeln($line);
        }

        return Command::SUCCESS;
    }
}
