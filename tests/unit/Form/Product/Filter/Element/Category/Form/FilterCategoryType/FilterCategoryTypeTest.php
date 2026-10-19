<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Category\Form\FilterCategoryType;

use App\Form\Product\Filter\Element\Category\Form\FilterCategoryType\FilterCategoryType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(FilterCategoryType::class)]
class FilterCategoryTypeTest extends MockeryTestCase
{
    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $filterCategoryType = new FilterCategoryType();
        $filterCategoryType->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertFalse($options['mapped']);
        $this->assertFalse($options['required']);
    }
}
