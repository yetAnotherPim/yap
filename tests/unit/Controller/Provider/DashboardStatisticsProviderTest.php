<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Provider;

use App\Controller\Provider\DashboardStatisticsProvider;
use App\Messenger\QueueMonitor\QueueMonitor;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DashboardStatisticsProvider::class)]
class DashboardStatisticsProviderTest extends MockeryTestCase
{
    /** @var ProductRepository|MockInterface */
    private ProductRepository $productRepository;

    /** @var ProductTypeRepository|MockInterface */
    private ProductTypeRepository $productTypeRepository;

    /** @var FieldTypeRepository|MockInterface */
    private FieldTypeRepository $fieldTypeRepository;

    /** @var UserRepository|MockInterface */
    private UserRepository $userRepository;

    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepository;

    /** @var IdentifierTypeRepository|MockInterface */
    private IdentifierTypeRepository $identifierTypeRepository;

    private QueueMonitor $queueMonitor;

    private DashboardStatisticsProvider $provider;

    protected function setUp(): void
    {
        $this->productRepository = \Mockery::mock(ProductRepository::class);
        $this->productTypeRepository = \Mockery::mock(ProductTypeRepository::class);
        $this->fieldTypeRepository = \Mockery::mock(FieldTypeRepository::class);
        $this->userRepository = \Mockery::mock(UserRepository::class);
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->identifierTypeRepository = \Mockery::mock(IdentifierTypeRepository::class);
        $this->queueMonitor = \Mockery::mock(QueueMonitor::class);

        $this->provider = new DashboardStatisticsProvider(
            $this->productRepository,
            $this->productTypeRepository,
            $this->fieldTypeRepository,
            $this->userRepository,
            $this->localeRepository,
            $this->identifierTypeRepository,
            $this->queueMonitor,
        );

        parent::setUp();
    }

    public function testGetStatisticsReturnsExpectedData(): void
    {
        $this->productRepository->shouldReceive('count')->with([])->once()->andReturn(10);
        $this->productRepository->shouldReceive('countCreatedWithinLastDays')->with(10)->once()->andReturn(2);
        $this->localeRepository->shouldReceive('count')->with([])->once()->andReturn(3);
        $this->identifierTypeRepository->shouldReceive('count')->with([])->once()->andReturn(4);
        $this->productTypeRepository->shouldReceive('count')->with([])->once()->andReturn(5);
        $this->fieldTypeRepository->shouldReceive('count')->with([])->once()->andReturn(6);
        $this->userRepository->shouldReceive('count')->with([])->once()->andReturn(7);

        $result = $this->provider->getStatistics();

        $expected = [
            'product.entity.title_plural' => 10,
            'dashboard.products.latest' => 2,
            'locale.entity.title_plural' => 3,
            'identifier_type.entity.title_plural' => 4,
            'product_type.entity.title_plural' => 5,
            'field_type.entity.title_plural' => 6,
            'user.entity.title_plural' => 7,
        ];

        $this->assertSame($expected, $result);
    }
}
