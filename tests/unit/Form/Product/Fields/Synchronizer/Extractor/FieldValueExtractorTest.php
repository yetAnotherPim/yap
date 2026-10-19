<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Synchronizer\Extractor;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldValueExtractor::class)]
class FieldValueExtractorTest extends MockeryTestCase
{
    public function testExtractReturnsOneForCheckedCheckbox(): void
    {
        $fieldType = $this->createFieldType('marketplace_ebay', InputType::CheckBox);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_marketplace_ebay' => '1',
            ],
        );

        $this->assertSame('1', $result);
    }

    public function testExtractReturnsZeroForUncheckedCheckbox(): void
    {
        $fieldType = $this->createFieldType('marketplace_ebay', InputType::CheckBox);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract($fieldType, []);

        $this->assertSame('', $result);
    }

    public function testExtractReturnsUploadedFilePath(): void
    {
        $fieldType = $this->createFieldType('manual', InputType::File);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [],
            [
                'field_manual' => '2026/09/manual.pdf',
            ],
        );

        $this->assertSame('2026/09/manual.pdf', $result);
    }

    public function testExtractReturnsNullWhenUploadedFileIsMissing(): void
    {
        $fieldType = $this->createFieldType('manual', InputType::File);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract($fieldType, []);

        $this->assertNull($result);
    }

    public function testExtractReturnsUploadedImagePath(): void
    {
        $fieldType = $this->createFieldType('product_image', InputType::Image);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [],
            [
                'field_product_image' => '2026/09/image.webp',
            ],
        );

        $this->assertSame('2026/09/image.webp', $result);
    }

    public function testExtractJoinsMultiSelectValues(): void
    {
        $fieldType = $this->createFieldType('marketplaces', InputType::MultiSelect);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_marketplaces' => [
                    'amazon',
                    'ebay',
                ],
            ],
        );

        $this->assertSame('amazon,ebay', $result);
    }

    public function testExtractReturnsNullForMissingMultiSelectValue(): void
    {
        $fieldType = $this->createFieldType('marketplaces', InputType::MultiSelect);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract($fieldType, []);

        $this->assertNull($result);
    }

    public function testExtractReturnsStringValue(): void
    {
        $fieldType = $this->createFieldType('description', InputType::Text);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_description' => 'Some description',
            ],
        );

        $this->assertSame('Some description', $result);
    }

    public function testExtractConvertsIntegerToString(): void
    {
        $fieldType = $this->createFieldType('storage', InputType::Integer);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_storage' => 256,
            ],
        );

        $this->assertSame('256', $result);
    }

    public function testExtractReturnsNullForEmptyString(): void
    {
        $fieldType = $this->createFieldType('description', InputType::Text);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_description' => '',
            ],
        );

        $this->assertNull($result);
    }

    public function testExtractReturnsNullForMissingValue(): void
    {
        $fieldType = $this->createFieldType('description', InputType::Text);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract($fieldType, []);

        $this->assertSame('', $result);
    }

    public function testExtractReturnsEmptyStringForUnexpectedArrayValue(): void
    {
        $fieldType = $this->createFieldType('description', InputType::Text);

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_description' => ['unexpected'],
            ],
        );

        $this->assertSame('', $result);
    }

    public function testExtractUsesLocaleSuffix(): void
    {
        $fieldType = $this->createFieldType('description', InputType::Text);

        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $extractor = new FieldValueExtractor();

        $result = $extractor->extract(
            $fieldType,
            [
                'field_description-de_DE' => 'Deutsche Beschreibung',
            ],
            [],
            $locale,
        );

        $this->assertSame('Deutsche Beschreibung', $result);
    }

    private function createFieldType(string $code, InputType $inputType): FieldType
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn($code);

        $fieldType
            ->shouldReceive('getInputType')
            ->andReturn($inputType);

        return $fieldType;
    }
}
