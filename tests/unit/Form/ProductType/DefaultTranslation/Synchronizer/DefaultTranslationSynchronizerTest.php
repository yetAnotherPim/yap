<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\ProductType\DefaultTranslation\Synchronizer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use App\Form\ProductType\DefaultTranslation\Synchronizer\DefaultTranslationSynchronizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DefaultTranslationSynchronizer::class)]
final class DefaultTranslationSynchronizerTest extends MockeryTestCase
{
    private DefaultTranslationResolver&MockInterface $resolver;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private FieldType&MockInterface $fieldType;

    private DefaultTranslationSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = \Mockery::mock(
            DefaultTranslationResolver::class,
        );

        $this->configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class,
        );

        $this->fieldType = \Mockery::mock(
            FieldType::class,
        );

        $this->synchronizer =
            new DefaultTranslationSynchronizer(
                $this->resolver,
            );
    }

    public function testSynchronizeSetsTranslationValue(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $this->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(
                $this->configuration,
                $locale,
            )
            ->andReturn($translation);

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Default Title');

        /** @var Collection<int, Locale> $locales */
        $locales = new ArrayCollection([
            $locale,
        ]);

        $this->synchronizer->synchronize(
            $this->configuration,
            $locales,
            [
                'de_DE' => 'Default Title',
            ],
        );
    }

    public function testSynchronizeSetsNullForEmptyValue(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $this->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(
                $this->configuration,
                $locale,
            )
            ->andReturn($translation);

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with(null);

        /** @var Collection<int, Locale> $locales */
        $locales = new ArrayCollection([
            $locale,
        ]);

        $this->synchronizer->synchronize(
            $this->configuration,
            $locales,
            [
                'de_DE' => '',
            ],
        );
    }

    public function testSynchronizeIgnoresUnknownLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($this->fieldType);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('es_ES');

        $this->resolver
            ->shouldNotReceive('resolve');

        /** @var Collection<int, Locale> $locales */
        $locales = new ArrayCollection([
            $locale,
        ]);

        $this->synchronizer->synchronize(
            $this->configuration,
            $locales,
            [
                'de_DE' => 'Default Title',
            ],
        );
    }

    public function testSynchronizeDoesNothingForNonTranslatableFieldType(): void
    {
        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($this->fieldType);

        $this->resolver
            ->shouldNotReceive('resolve');

        $this->synchronizer->synchronize(
            $this->configuration,
            new ArrayCollection(),
            [
                'de_DE' => 'Default Title',
            ],
        );
    }

    public function testSynchronizeDoesNothingWithoutFieldType(): void
    {
        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $this->fieldType
            ->shouldNotReceive('isTranslatable');

        $this->resolver
            ->shouldNotReceive('resolve');

        $this->synchronizer->synchronize(
            $this->configuration,
            new ArrayCollection(),
            [
                'de_DE' => 'Default Title',
            ],
        );
    }

    public function testSynchronizeIgnoresNonStringLocaleId(): void
    {
        $this->configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($this->fieldType);

        $this->resolver
            ->shouldNotReceive('resolve');

        $this->synchronizer->synchronize(
            $this->configuration,
            new ArrayCollection(),
            [
                0 => 'Default Title',
            ],
        );
    }
}
