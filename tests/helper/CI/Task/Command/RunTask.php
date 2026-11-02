<?php

declare(strict_types=1);

namespace App\Tests\helper\CI\Task\Command;

use App\Tests\helper\CI\Task\Task;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ci:run-task',
)]
class RunTask extends Command
{
    public function __construct(
        private readonly Task $task,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        ($this->task)();

        return Command::SUCCESS;
    }
}
