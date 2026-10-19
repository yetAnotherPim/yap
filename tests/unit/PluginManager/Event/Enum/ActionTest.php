<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\Enum;

use App\PluginManager\Event\Enum\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Action::class)]
class ActionTest extends TestCase
{
    public function testTryFromName(): void
    {
        $this->assertEquals(Action::UPDATE, Action::tryFromName('update'));
        $this->assertEquals(Action::ACTIVATE, Action::tryFromName('activate'));
        $this->assertEquals(Action::DEACTIVATE, Action::tryFromName('deactivate'));
        $this->assertNull(Action::tryFromName('something'));
    }
}
