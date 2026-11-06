<?php

declare(strict_types=1);

namespace App\DemoData\Command;

use App\DemoData\Generator\DemoDataGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'yap:demo-data',
    description: 'Generates additional demo products and reference data.',
)]
class DemoDataCommand extends Command
{
    private const int DEFAULT_COUNT = 100;

    public function __construct(
        private readonly DemoDataGenerator $demoDataGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'count',
            'c',
            InputOption::VALUE_REQUIRED,
            'Number of additional products to generate.',
            (string) self::DEFAULT_COUNT,
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $count = filter_var(
            $input->getOption('count'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if (false === $count) {
            $io->error('The count must be a positive integer.');

            return Command::INVALID;
        }

        $io->title('YAP Demo Data Generator');

        $io->text(\sprintf(
            'Generating %d additional products...',
            $count,
        ));

        $io->progressStart($count);

        $this->demoDataGenerator->generate(
            $count,
            static function (int $progress) use ($io): void {
                $io->progressAdvance();
            },
        );

        $io->progressFinish();

        $io->success(\sprintf(
            '%d additional demo products have been generated.',
            $count,
        ));

        return Command::SUCCESS;
    }
}
