<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Provider\Dto;

use App\Controller\Provider\Dto\DashboardStatistic;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DashboardStatistic::class)]
class DashboardStatisticTest extends MockeryTestCase
{
    public function testConstructorSetsValueAndColor(): void
    {
        $statistic = new DashboardStatistic(
            value: 42,
            color: 'info',
        );

        $this->assertSame(42, $statistic->value);
        $this->assertSame('info', $statistic->color);
    }

    public function testColorDefaultsToNull(): void
    {
        $statistic = new DashboardStatistic(value: 0);

        $this->assertSame(0, $statistic->value);
        $this->assertNull($statistic->color);
    }
}
