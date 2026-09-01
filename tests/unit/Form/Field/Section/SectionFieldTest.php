<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Field\Section;

use App\Form\Field\Section\SectionField;
use App\Form\Field\Section\Type\SectionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionField::class)]
class SectionFieldTest extends TestCase
{
    public function testNew(): void
    {
        $field = SectionField::new('test_section', 'Test Label');

        $this->assertSame('test_section', $field->getAsDto()->getProperty());
        $this->assertSame('Test Label', $field->getAsDto()->getLabel());
        $this->assertSame('field-section', $field->getAsDto()->getCssClass());
        $this->assertSame(SectionType::class, $field->getAsDto()->getFormType());
    }

    public function testNewWithoutLabel(): void
    {
        $field = SectionField::new('test_section');

        $this->assertSame('test_section', $field->getAsDto()->getProperty());
        $this->assertNull($field->getAsDto()->getLabel());
    }
}
