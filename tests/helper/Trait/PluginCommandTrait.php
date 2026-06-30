<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use Symfony\Component\Console\Tester\ApplicationTester;

trait PluginCommandTrait
{
    protected ApplicationTester $applicationTester;

    protected function refreshPlugins(bool $expectSuccess = false): void
    {
        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugins:list',
                    '--refresh' => true,
                ]
            );

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
                ]
            );

        if (true === $expectSuccess) {
            $this->applicationTester->assertCommandIsSuccessful();
        }
    }
}
