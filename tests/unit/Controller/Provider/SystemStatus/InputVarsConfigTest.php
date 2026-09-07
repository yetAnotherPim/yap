<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\InputVarsConfig;
use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Entity\Product\Type\ProductType;
use App\Repository\LocaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(InputVarsConfig::class)]
#[UsesClass(SystemStatusMessage::class)]
class InputVarsConfigTest extends MockeryTestCase
{
    /** @var EntityManagerInterface|MockInterface */
    private EntityManagerInterface $entityManager;

    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepository;

    private InputVarsConfig $checker;

    protected function setUp(): void
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);

        $this->checker = new InputVarsConfig($this->entityManager, $this->localeRepository);

        parent::setUp();
    }

    public function testCheckReturnsWarningWhenInputVarsExceedThreshold(): void
    {
        $maxInputVars = (int) \ini_get('max_input_vars');
        $localesCount = 2;
        // We want $inputVars > $maxInputVars - 10
        // $inputVars = $fieldCount + ($translatableFieldCount * $localesCount)
        // Let's set $translatableFieldCount = 100
        // Then $inputVars = $fieldCount + 200
        // If $maxInputVars is 1000, we need $fieldCount + 200 > 990 => $fieldCount > 790

        $translatableFieldCount = 100;
        $fieldCount = $maxInputVars - ($translatableFieldCount * $localesCount) + 1;

        if ($fieldCount < 0) {
            $fieldCount = 0;
            $translatableFieldCount = (int) ceil(($maxInputVars - 9) / $localesCount);
        }

        $expectedInputVars = $fieldCount + ($translatableFieldCount * $localesCount);

        $this->localeRepository->shouldReceive('count')
            ->with(['contentTranslationEnabled' => true])
            ->once()
            ->andReturn($localesCount);

        $qb = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);

        $this->entityManager->shouldReceive('createQueryBuilder')->once()->andReturn($qb);
        $qb->shouldReceive('select')->with('pt.code AS code')->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('pt.title AS title')->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('COUNT(c.id) AS fieldCount')->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('SUM(CASE WHEN ft.translatable = true THEN 1 ELSE 0 END) AS translatableFieldCount')->once()->andReturnSelf();
        $qb->shouldReceive('from')->with(ProductType::class, 'pt')->once()->andReturnSelf();
        $qb->shouldReceive('leftJoin')->with('pt.productTypeFieldConfigurations', 'c')->once()->andReturnSelf();
        $qb->shouldReceive('leftJoin')->with('c.fieldType', 'ft')->once()->andReturnSelf();
        $qb->shouldReceive('groupBy')->with('pt.id')->once()->andReturnSelf();
        $qb->shouldReceive('addGroupBy')->with('pt.code')->once()->andReturnSelf();
        $qb->shouldReceive('addGroupBy')->with('pt.title')->once()->andReturnSelf();
        $qb->shouldReceive('getQuery')->once()->andReturn($query);

        $query->shouldReceive('getArrayResult')->once()->andReturn([
            [
                'code' => 'P1',
                'title' => 'Product 1',
                'fieldCount' => $fieldCount,
                'translatableFieldCount' => $translatableFieldCount,
            ],
        ]);

        $result = $this->checker->check();

        $this->assertCount(1, $result);
        $this->assertSame(MessageType::Warning, $result[0]->getType());

        /** @var TranslatableMessage $msg */
        $msg = $result[0]->getMessage();
        $this->assertSame('dashboard.system_status.input_vars.message', $msg->getMessage());
        $parameters = $msg->getParameters();
        $this->assertSame('Product 1', $parameters['%productType%']);
        $this->assertSame($expectedInputVars, $parameters['%fieldCount%']);
        $this->assertSame($maxInputVars, $parameters['%maxInputVars%']);
    }

    public function testCheckReturnsEmptyWhenBelowThreshold(): void
    {
        $maxInputVars = (int) \ini_get('max_input_vars');

        $this->localeRepository->shouldReceive('count')->once()->andReturn(0);

        $qb = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $this->entityManager->shouldReceive('createQueryBuilder')->andReturn($qb);
        $qb->shouldReceive('select', 'addSelect', 'from', 'leftJoin', 'groupBy', 'addGroupBy')->andReturnSelf();
        $qb->shouldReceive('getQuery')->andReturn($query);

        $query->shouldReceive('getArrayResult')->once()->andReturn([
            [
                'code' => 'P1',
                'title' => 'Product 1',
                'fieldCount' => max(0, $maxInputVars - 11),
                'translatableFieldCount' => 0,
            ],
        ]);

        $result = $this->checker->check();
        $this->assertCount(0, $result);
    }
}
