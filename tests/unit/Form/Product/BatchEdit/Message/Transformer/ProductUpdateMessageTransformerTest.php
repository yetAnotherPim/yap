<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Message\Transformer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use App\Form\Product\BatchEdit\Message\Transformer\ProductUpdateMessageTransformer;
use App\Messenger\ProductUpdate\Message\Dto\FieldIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Enum\ProductFieldIdentifierType;
use App\Messenger\ProductUpdate\Message\UpdateProductMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProductUpdateMessageTransformer::class)]
#[CoversClass(FieldIdentifier::class)]
#[CoversClass(ProductField::class)]
#[CoversClass(FieldTranslation::class)]
#[CoversClass(UpdateProductMessage::class)]
class ProductUpdateMessageTransformerTest extends MockeryTestCase
{
    private ProductUpdateMessageTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new ProductUpdateMessageTransformer();
    }

    public function testTransformMapsProductIdAndField(): void
    {
        $fieldTypeId = Uuid::v4();
        $fieldType = $this->createFieldTypeMock($fieldTypeId, false);
        $fieldData = $this->createFieldDataMock($fieldType, 'Example value');
        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertSame('product-123', $message->getProductId());
        $this->assertCount(1, $message->getFields());

        $field = $this->getFirstField($message);

        $this->assertSame('Example value', $field->getValue());
        $this->assertSame(
            ProductFieldIdentifierType::ID,
            $field->getIdentifier()->getType(),
        );
        $this->assertSame(
            (string) $fieldTypeId,
            $field->getIdentifier()->getValue(),
        );
        $this->assertCount(0, $field->getTranslations());
    }

    public function testTransformPreservesNullFieldValue(): void
    {
        $fieldType = $this->createFieldTypeMock(Uuid::v4(), false);
        $fieldData = $this->createFieldDataMock($fieldType, null);
        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertCount(1, $message->getFields());
        $this->assertNull($this->getFirstField($message)->getValue());
    }

    public function testTransformSkipsFieldWithoutFieldType(): void
    {
        $fieldData = $this->createFieldDataMock(null, null);
        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertSame('product-123', $message->getProductId());
        $this->assertCount(0, $message->getFields());
    }

    public function testTransformMapsTranslationsForTranslatableField(): void
    {
        $fieldType = $this->createFieldTypeMock(Uuid::v4(), true);
        $localeId = 'locale-de';

        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn($localeId);

        $translationData = $this->createTranslationDataMock(
            $locale,
            'Deutscher Wert',
        );

        $fieldData = $this->createFieldDataMock(
            $fieldType,
            'Default value',
            [$translationData],
        );

        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertCount(1, $message->getFields());

        $field = $this->getFirstField($message);

        $this->assertSame('Default value', $field->getValue());
        $this->assertCount(1, $field->getTranslations());

        $translation = $this->getFirstTranslation($field);

        $this->assertSame($localeId, $translation->getLocaleId());
        $this->assertSame('Deutscher Wert', $translation->getValue());
    }

    public function testTransformSkipsTranslationsWithoutLocale(): void
    {
        $fieldType = $this->createFieldTypeMock(Uuid::v4(), true);

        $translationData = $this->createTranslationDataMock(
            null,
            'Value without locale',
        );

        $fieldData = $this->createFieldDataMock(
            $fieldType,
            null,
            [$translationData],
        );

        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertCount(1, $message->getFields());
        $this->assertCount(
            0,
            $this->getFirstField($message)->getTranslations(),
        );
    }

    public function testTransformIgnoresTranslationsForNonTranslatableField(): void
    {
        $fieldType = $this->createFieldTypeMock(Uuid::v4(), false);

        $translationData = $this->createTranslationDataMock(
            null,
            'Should be ignored',
        );

        $fieldData = $this->createFieldDataMock(
            $fieldType,
            null,
            [$translationData],
        );

        $data = $this->createBatchEditDataMock([$fieldData]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertCount(1, $message->getFields());
        $this->assertCount(
            0,
            $this->getFirstField($message)->getTranslations(),
        );
    }

    public function testTransformMapsMultipleFields(): void
    {
        $firstId = Uuid::fromString('11111111-1111-4111-8111-111111111111');
        $secondId = Uuid::fromString('22222222-2222-4222-8222-222222222222');

        $firstFieldData = $this->createFieldDataMock(
            $this->createFieldTypeMock($firstId, false),
            'First',
        );

        $secondFieldData = $this->createFieldDataMock(
            $this->createFieldTypeMock($secondId, false),
            'Second',
        );

        $data = $this->createBatchEditDataMock([
            $firstFieldData,
            $secondFieldData,
        ]);

        $message = $this->transformer->transform($data, 'product-123');

        $this->assertCount(2, $message->getFields());

        $this->assertSame(
            [(string) $firstId, (string) $secondId],
            $message->getFields()
                ->map(
                    static fn (ProductField $field): string => $field->getIdentifier()->getValue(),
                )
                ->toArray(),
        );

        $this->assertSame(
            ['First', 'Second'],
            $message->getFields()
                ->map(
                    static fn (ProductField $field): ?string => $field->getValue(),
                )
                ->toArray(),
        );
    }

    /**
     * @param list<BatchEditFieldData> $fields
     */
    private function createBatchEditDataMock(array $fields): BatchEditData
    {
        $data = \Mockery::mock(BatchEditData::class);

        $data
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection($fields));

        return $data;
    }

    /**
     * @param list<BatchEditFieldTranslationData> $translations
     */
    private function createFieldDataMock(
        ?FieldType $fieldType,
        ?string $value,
        array $translations = [],
    ): BatchEditFieldData {
        $fieldData = \Mockery::mock(BatchEditFieldData::class);

        $fieldData
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        if (null === $fieldType) {
            return $fieldData;
        }

        $fieldData
            ->shouldReceive('getValue')
            ->once()
            ->andReturn($value);

        $fieldData
            ->shouldReceive('getTranslations')
            ->zeroOrMoreTimes()
            ->andReturn(new ArrayCollection($translations));

        return $fieldData;
    }

    private function createTranslationDataMock(
        ?Locale $locale,
        ?string $value,
    ): BatchEditFieldTranslationData {
        $translationData = \Mockery::mock(
            BatchEditFieldTranslationData::class,
        );

        $translationData
            ->shouldReceive('getLocale')
            ->zeroOrMoreTimes()
            ->andReturn($locale);

        $translationData
            ->shouldReceive('getValue')
            ->zeroOrMoreTimes()
            ->andReturn($value);

        return $translationData;
    }

    private function createFieldTypeMock(
        Uuid $id,
        bool $translatable,
    ): FieldType {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn($id);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturn($translatable);

        return $fieldType;
    }

    private function getFirstField(UpdateProductMessage $message): ProductField
    {
        $field = $message->getFields()->first();

        $this->assertInstanceOf(ProductField::class, $field);

        return $field;
    }

    private function getFirstTranslation(ProductField $field): FieldTranslation
    {
        $translation = $field->getTranslations()->first();

        $this->assertInstanceOf(FieldTranslation::class, $translation);

        return $translation;
    }
}
