<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Command;

use App\DemoData\Command\DemoDataCommand;
use App\DemoData\Generator\DemoDataGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DemoDataCommand::class)]
class DemoDataCommandTest extends MockeryTestCase
{
    public function testExecuteUsesDefaultCount(): void
    {
        $generator = \Mockery::mock(DemoDataGenerator::class);

        $generator
            ->shouldReceive('generate')
            ->once()
            ->with(
                100,
                \Mockery::type('callable'),
            )
            ->andReturnUsing(
                static function (int $count, callable $progress): void {
                    $progress(1);
                },
            );

        $commandTester = new CommandTester(
            new DemoDataCommand($generator),
        );

        $exitCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString(
            'Generating 100 additional products...',
            $commandTester->getDisplay(),
        );
        $this->assertStringContainsString(
            '100 additional demo products have been generated.',
            $commandTester->getDisplay(),
        );
    }

    public function testExecuteUsesProvidedCount(): void
    {
        $generator = \Mockery::mock(DemoDataGenerator::class);

        $generator
            ->shouldReceive('generate')
            ->once()
            ->with(25, \Mockery::type('callable'));

        $commandTester = new CommandTester(
            new DemoDataCommand($generator),
        );

        $exitCode = $commandTester->execute([
            '--count' => '25',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString(
            'Generating 25 additional products...',
            $commandTester->getDisplay(),
        );
        $this->assertStringContainsString(
            '25 additional demo products have been generated.',
            $commandTester->getDisplay(),
        );
    }

    public function testExecuteAcceptsCountOptionShortcut(): void
    {
        $generator = \Mockery::mock(DemoDataGenerator::class);

        $generator
            ->shouldReceive('generate')
            ->once()
            ->with(10, \Mockery::type('callable'));

        $commandTester = new CommandTester(
            new DemoDataCommand($generator),
        );

        $exitCode = $commandTester->execute([
            '-c' => '10',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    #[DataProvider('provideInvalidCounts')]
    public function testExecuteRejectsInvalidCount(string $count): void
    {
        $generator = \Mockery::mock(DemoDataGenerator::class);
        $generator->shouldNotReceive('generate');

        $commandTester = new CommandTester(
            new DemoDataCommand($generator),
        );

        $exitCode = $commandTester->execute([
            '--count' => $count,
        ]);

        $this->assertSame(Command::INVALID, $exitCode);
        $this->assertStringContainsString(
            'The count must be a positive integer.',
            $commandTester->getDisplay(),
        );
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public static function provideInvalidCounts(): iterable
    {
        yield 'zero' => ['0'];
        yield 'negative number' => ['-1'];
        yield 'decimal number' => ['1.5'];
        yield 'non-numeric value' => ['abc'];
        yield 'empty value' => [''];
    }

    public function testExecuteAdvancesProgressForGeneratorCallback(): void
    {
        $generator = \Mockery::mock(DemoDataGenerator::class);

        $generator
            ->shouldReceive('generate')
            ->once()
            ->with(3, \Mockery::type('callable'))
            ->andReturnUsing(
                static function (int $count, callable $progress): void {
                    $progress(1);
                    $progress(2);
                    $progress(3);
                },
            );

        $commandTester = new CommandTester(
            new DemoDataCommand($generator),
        );

        $exitCode = $commandTester->execute([
            '--count' => '3',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString(
            '3/3',
            $commandTester->getDisplay(),
        );
    }
}
