<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Provider;

use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Controller\Provider\SystemStatus\SystemCheckInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DashboardSystemStatusProvider::class)]
class DashboardSystemStatusProviderTest extends MockeryTestCase
{
    public function testGetMessagesAggregatesResultsFromAllChecks(): void
    {
        $check1 = \Mockery::mock(SystemCheckInterface::class);
        $check1->shouldReceive('check')->once()->andReturn(['msg1', 'msg2']);

        $check2 = \Mockery::mock(SystemCheckInterface::class);
        $check2->shouldReceive('check')->once()->andReturn(['msg3']);

        $provider = new DashboardSystemStatusProvider([$check1, $check2]);

        $result = $provider->getMessages();

        $this->assertSame(['msg1', 'msg2', 'msg3'], $result);
    }

    public function testGetMessagesReturnsEmptyArrayWhenNoChecks(): void
    {
        $provider = new DashboardSystemStatusProvider([]);

        $result = $provider->getMessages();

        $this->assertSame([], $result);
    }
}
