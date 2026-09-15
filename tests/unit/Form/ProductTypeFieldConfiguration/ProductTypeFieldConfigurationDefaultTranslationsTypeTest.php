<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\ProductTypeFieldConfiguration;

use App\Entity\Locale\Locale;
use App\Form\ProductTypeFieldConfiguration\ProductTypeFieldConfigurationDefaultTranslationsType;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(ProductTypeFieldConfigurationDefaultTranslationsType::class)]
class ProductTypeFieldConfigurationDefaultTranslationsTypeTest extends MockeryTestCase
{
    public function testBuildFormAddsTextareaForEveryAvailableContentTranslationLocale(): void
    {
        $de = \Mockery::mock(Locale::class);
        $de
            ->shouldReceive('getId')
            ->twice()
            ->andReturn('de_DE');

        $es = \Mockery::mock(Locale::class);
        $es
            ->shouldReceive('getId')
            ->twice()
            ->andReturn('es_ES');

        $locales = new ArrayCollection([
            $de,
            $es,
        ]);

        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn($locales);

        $builder = \Mockery::mock(FormBuilderInterface::class);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'de_DE',
                TextareaType::class,
                [
                    'label' => 'de_DE',
                    'required' => false,
                    'mapped' => false,
                ],
            )
            ->andReturnSelf();

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'es_ES',
                TextareaType::class,
                [
                    'label' => 'es_ES',
                    'required' => false,
                    'mapped' => false,
                ],
            )
            ->andReturnSelf();

        $type = new ProductTypeFieldConfigurationDefaultTranslationsType(
            $localeRepository,
        );

        $type->buildForm($builder, []);
    }

    public function testBuildFormAddsNoFieldsWhenNoContentTranslationLocalesAreAvailable(): void
    {
        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection());

        $builder = \Mockery::mock(FormBuilderInterface::class);
        $builder
            ->shouldNotReceive('add');

        $type = new ProductTypeFieldConfigurationDefaultTranslationsType(
            $localeRepository,
        );

        $type->buildForm($builder, []);
    }

    public function testConfigureOptionsDefinesCollectionOptions(): void
    {
        $localeRepository = \Mockery::mock(LocaleRepository::class);

        $resolver = \Mockery::mock(OptionsResolver::class);

        $resolver
            ->shouldReceive('setDefined')
            ->once()
            ->with([
                'allow_add',
                'allow_delete',
                'delete_empty',
                'entry_options',
                'entry_type',
                'prototype_data',
                'prototype_name',
                'prototype_options',
            ])
            ->andReturnSelf();

        $type = new ProductTypeFieldConfigurationDefaultTranslationsType(
            $localeRepository,
        );

        $type->configureOptions($resolver);
    }
}
