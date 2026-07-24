<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\HttpKernel\KernelInterface;

trait PluginCommandTrait
{
    protected ApplicationTester $applicationTester;

    protected function refreshPlugins(KernelInterface $kernel, bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugins:list',
                    '--refresh' => true,
                    '--env' => 'ct',
                ]
            );

        // reload kernel cuz otherwise phpunit tries to access deleted cache files
        $kernel->shutdown();
        $kernel->boot();

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }

    protected function listPlugins(bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugins:list',
                    '--env' => 'ct',
                ]
            );

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }

    protected function activatePlugin(string $pluginName, bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugin:activate',
                    'plugin-name' => $pluginName,
                    '--env' => 'ct',
                ]
            );

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }

    protected function deactivatePlugin(string $pluginName, bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugin:deactivate',
                    'plugin-name' => $pluginName,
                    '--env' => 'ct',
                ]
            );

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }

    protected function updatePlugin(string $pluginName, bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugin:update',
                    'plugin-name' => $pluginName,
                    '--env' => 'ct',
                ]
            );

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }
}
