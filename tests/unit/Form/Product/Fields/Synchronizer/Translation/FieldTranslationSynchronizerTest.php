<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Synchronizer\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Resolver\FieldValueTranslationResolver;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTranslationSynchronizer::class)]
class FieldTranslationSynchronizerTest extends MockeryTestCase
{
    private LocaleRepository&MockInterface $localeRepository;

    private FieldValueTranslationResolver&MockInterface $fieldValueTranslationResolver;

    private FieldValueExtractor&MockInterface $fieldValueExtractor;
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;

    private FieldTranslationSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->fieldValueTranslationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $this->fieldValueExtractor = \Mockery::mock(FieldValueExtractor::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->synchronizer = new FieldTranslationSynchronizer(
            $this->localeRepository,
            $this->fieldValueTranslationResolver,
            $this->fieldValueExtractor,
            $this->defaultTranslationResolver
        );
    }

    public function testSynchronizeTranslationDoesNothingForNonTranslatableField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $productTypeFieldConfiguration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnFalse();

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);

        $this->localeRepository
            ->shouldNotReceive('getAvailableContentTranslationLocales');

        $this->fieldValueTranslationResolver
            ->shouldNotReceive('resolve');

        $this->fieldValueExtractor
            ->shouldNotReceive('extract');

        $field
            ->shouldNotReceive('addTranslation');

        $this->synchronizer->synchronizeTranslation($field, $productTypeFieldConfiguration);
    }

    public function testSynchronizeTranslationAddsTranslationWithoutChangingValueWhenRequestDataIsNull(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldValueTranslation::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $productTypeFieldConfiguration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $defaultTranslation = \Mockery::mock(ProductTypeFieldConfigurationDefaultTranslation::class);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $locale, 'default_translation_value')
            ->andReturn($translation);

        $this->fieldValueExtractor
            ->shouldNotReceive('extract');

        $this->defaultTranslationResolver
            ->shouldNotReceive('resolve')
            ->once()
            ->with($productTypeFieldConfiguration, $locale)
            ->andReturn($defaultTranslation);

        $defaultTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('default_translation_value');

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($translation);

        $this->synchronizer->synchronizeTranslation($field, $productTypeFieldConfiguration);
    }

    public function testSynchronizeTranslationExtractsAndSetsTranslatedValue(): void
    {
        $requestData = [
            'field_description-de_DE' => 'Deutsche Beschreibung',
        ];

        $uploadedFilePaths = [
            'field_image-de_DE' => '/tmp/image.jpg',
        ];

        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldValueTranslation::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $productTypeFieldConfiguration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $defaultTranslation = \Mockery::mock(ProductTypeFieldConfigurationDefaultTranslation::class);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($fieldType);

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $locale, 'default_translation_value')
            ->andReturn($translation);

        $this->fieldValueExtractor
            ->shouldReceive('extract')
            ->once()
            ->with(
                $fieldType,
                $requestData,
                $uploadedFilePaths,
                $locale,
            )
            ->andReturn('Deutsche Beschreibung');

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Deutsche Beschreibung')
            ->andReturnSelf();

        $this->defaultTranslationResolver
            ->shouldNotReceive('resolve')
            ->once()
            ->with($productTypeFieldConfiguration, $locale)
            ->andReturn($defaultTranslation);

        $defaultTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('default_translation_value');

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($translation);

        $this->synchronizer->synchronizeTranslation(
            $field,
            $productTypeFieldConfiguration,
            $requestData,
            $uploadedFilePaths,
        );
    }

    public function testSynchronizeTranslationSynchronizesAllLocales(): void
    {
        $productTypeFieldConfiguration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $defaultTranslationDe = \Mockery::mock(ProductTypeFieldConfigurationDefaultTranslation::class);
        $defaultTranslationFr = \Mockery::mock(ProductTypeFieldConfigurationDefaultTranslation::class);

        $deLocale = \Mockery::mock(Locale::class);
        $frLocale = \Mockery::mock(Locale::class);

        $deTranslation = \Mockery::mock(FieldValueTranslation::class);
        $frTranslation = \Mockery::mock(FieldValueTranslation::class);

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($fieldType);

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([
                $deLocale,
                $frLocale,
            ]));

        $this->defaultTranslationResolver
            ->shouldNotReceive('resolve')
            ->once()
            ->with($productTypeFieldConfiguration, $deLocale)
            ->andReturn($defaultTranslationDe);

        $this->defaultTranslationResolver
            ->shouldNotReceive('resolve')
            ->once()
            ->with($productTypeFieldConfiguration, $frLocale)
            ->andReturn($defaultTranslationFr);

        $defaultTranslationDe
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('default_translation_value_de');

        $defaultTranslationFr
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $deLocale, 'default_translation_value_de')
            ->andReturn($deTranslation);

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $frLocale, null)
            ->andReturn($frTranslation);

        $requestData = [
            'field_description-de_DE' => 'Beschreibung',
            'field_description-fr_FR' => 'Description',
        ];

        $this->fieldValueExtractor
            ->shouldReceive('extract')
            ->once()
            ->with($fieldType, $requestData, [], $deLocale)
            ->andReturn('Beschreibung');

        $this->fieldValueExtractor
            ->shouldReceive('extract')
            ->once()
            ->with($fieldType, $requestData, [], $frLocale)
            ->andReturn('Description');

        $deTranslation
            ->shouldReceive('setValue')
            ->once()
            ->with('Beschreibung')
            ->andReturnSelf();

        $frTranslation
            ->shouldReceive('setValue')
            ->once()
            ->with('Description')
            ->andReturnSelf();

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($deTranslation);

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($frTranslation);

        $this->synchronizer->synchronizeTranslation(
            $field,
            $productTypeFieldConfiguration,
            $requestData,
        );
    }
}
