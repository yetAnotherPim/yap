<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Identifier;

use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Form\Product\Filter\Identifier\IdentifierExistsFilter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;

#[CoversClass(IdentifierExistsFilter::class)]
class IdentifierExistsFilterTest extends MockeryTestCase
{
    public function testNew(): void
    {
        $id = Uuid::fromString(
            '01996b9d-15a2-7d10-a85a-18c99b8ed801',
        );

        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->once()
            ->andReturn($id);
        $identifierType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('EAN');

        $filter = IdentifierExistsFilter::new($identifierType);

        $dto = $filter->getAsDto();

        $this->assertSame(
            'has_identifier_01996b9d_15a2_7d10_a85a_18c99b8ed801',
            $dto->getProperty(),
        );

        $this->assertSame(
            BooleanFilterType::class,
            $dto->getFormType(),
        );

        $label = $dto->getLabel();

        $this->assertInstanceOf(
            TranslatableMessage::class,
            $label,
        );

        $this->assertSame(
            'filter.is_set',
            $label->getMessage(),
        );

        $this->assertSame(
            ['%name%' => 'EAN'],
            $label->getParameters(),
        );

        $this->assertSame(
            'admin',
            $label->getDomain(),
        );
    }

    #[DataProvider('provideExistsValues')]
    public function testApply(
        bool $value,
        string $expectedCondition,
    ): void {
        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->andReturn(
                Uuid::fromString(
                    '01996b9d-15a2-7d10-a85a-18c99b8ed801',
                ),
            );
        $identifierType
            ->shouldReceive('getTitle')
            ->andReturn('EAN');

        $filter = IdentifierExistsFilter::new($identifierType);

        $filterDataDto = FilterDataDto::new(
            0,
            $filter->getAsDto(),
            'entity',
            [
                'comparison' => '=',
                'value' => $value,
            ],
        );

        $parameterName = $filterDataDto->getParameterName();
        $identifierAlias = $parameterName.'_identifier';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('leftJoin')
            ->once()
            ->with(
                'entity.identifiers',
                $identifierAlias,
                'WITH',
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                $parameterName.'_type',
                $identifierType,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                $identifierAlias.'.id '.$expectedCondition,
            )
            ->andReturnSelf();

        $filter->apply(
            $queryBuilder,
            $filterDataDto,
            null,
            $this->createEntityDto(),
        );
    }

    /**
     * @return iterable<string, array{bool, string}>
     */
    public static function provideExistsValues(): iterable
    {
        yield 'identifier exists' => [
            true,
            'IS NOT NULL',
        ];

        yield 'identifier does not exist' => [
            false,
            'IS NULL',
        ];
    }

    /**
     * @return EntityDto<object>
     *
     * @throws \ReflectionException
     */
    private function createEntityDto(): EntityDto
    {
        return new \ReflectionClass(EntityDto::class)
            ->newInstanceWithoutConstructor();
    }
}
