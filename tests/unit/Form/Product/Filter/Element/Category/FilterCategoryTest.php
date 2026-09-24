<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Category;

use App\Form\Product\Filter\Element\Category\FilterCategory;
use App\Form\Product\Filter\Element\Category\Form\FilterCategoryType\FilterCategoryType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(FilterCategory::class)]
class FilterCategoryTest extends MockeryTestCase
{
    public function testNew(): void
    {
        $label = new TranslatableMessage(
            'filter.labels.fields',
            domain: 'admin',
        );

        $filter = FilterCategory::new(
            '_fields',
            $label,
        );

        $dto = $filter->getAsDto();

        $this->assertSame(
            FilterCategory::class,
            $dto->getFqcn(),
        );
        $this->assertSame(
            '_fields',
            $dto->getProperty(),
        );
        $this->assertSame(
            $label,
            $dto->getLabel(),
        );
        $this->assertSame(
            FilterCategoryType::class,
            $dto->getFormType(),
        );
        $this->assertSame(
            [
                'label' => $label,
                'label_attr' => [
                    'class' => 'filter-category',
                ],
            ],
            $dto->getFormTypeOptions(),
        );
    }

    public function testApplyDoesNotModifyQueryBuilder(): void
    {
        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        FilterCategory::new('_fields', 'Fields')->apply(
            $queryBuilder,
            $this->createFilterDataDto(),
            null,
            $this->createEntityDto(),
        );

        $this->addToAssertionCount(1);
    }

    private function createFilterDataDto(): FilterDataDto
    {
        return (new \ReflectionClass(FilterDataDto::class))
            ->newInstanceWithoutConstructor();
    }

    /**
     * @return EntityDto<object>
     */
    private function createEntityDto(): EntityDto
    {
        /** @var EntityDto<object> $entityDto */
        $entityDto = (new \ReflectionClass(EntityDto::class))
            ->newInstanceWithoutConstructor();

        return $entityDto;
    }
}
