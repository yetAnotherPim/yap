<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\SamplePlugin\Command;

use App\plugins\SamplePlugin\Command\SampleCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SampleCommand::class)]
class SampleCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $command = new SampleCommand();
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
