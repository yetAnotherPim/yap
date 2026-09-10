<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Synchronizer\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Resolver\FieldValueTranslationResolver;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
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

    private FieldTranslationSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->fieldValueTranslationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $this->fieldValueExtractor = \Mockery::mock(FieldValueExtractor::class);

        $this->synchronizer = new FieldTranslationSynchronizer(
            $this->localeRepository,
            $this->fieldValueTranslationResolver,
            $this->fieldValueExtractor,
        );
    }

    public function testSynchronizeTranslationDoesNothingForNonTranslatableField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
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

        $this->synchronizer->synchronizeTranslation($field);
    }

    public function testSynchronizeTranslationAddsTranslationWithoutChangingValueWhenRequestDataIsNull(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldValueTranslation::class);
        $fieldType = \Mockery::mock(FieldType::class);

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
            ->with($field, $locale)
            ->andReturn($translation);

        $translation
            ->shouldNotReceive('setValue');

        $this->fieldValueExtractor
            ->shouldNotReceive('extract');

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($translation);

        $this->synchronizer->synchronizeTranslation($field);
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
            ->with($field, $locale)
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

        $field
            ->shouldReceive('addTranslation')
            ->once()
            ->with($translation);

        $this->synchronizer->synchronizeTranslation(
            $field,
            $requestData,
            $uploadedFilePaths,
        );
    }

    public function testSynchronizeTranslationSynchronizesAllLocales(): void
    {
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

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $deLocale)
            ->andReturn($deTranslation);

        $this->fieldValueTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($field, $frLocale)
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
            $requestData,
        );
    }
}
