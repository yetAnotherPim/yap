<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Field\Section\Type;

use App\Form\Field\Section\Type\SectionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(SectionType::class)]
class SectionTypeTest extends TestCase
{
    private SectionType $type;

    protected function setUp(): void
    {
        $this->type = new SectionType();
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        $this->assertArrayHasKey('mapped', $options);
        $this->assertFalse($options['mapped']);
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('separator', $this->type->getBlockPrefix());
    }
}
