<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\ProductType\DefaultTranslation\Type;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use App\Form\ProductType\DefaultTranslation\Type\DefaultTranslationsType;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(DefaultTranslationsType::class)]
final class DefaultTranslationsTypeTest extends MockeryTestCase
{
    private LocaleRepository&MockInterface $localeRepository;

    private DefaultTranslationResolver&MockInterface $resolver;

    /**
     * @var FormBuilderInterface<array<string, ?string>|null>&MockInterface
     */
    private FormBuilderInterface&MockInterface $builder;

    private DefaultTranslationsType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeRepository = \Mockery::mock(
            LocaleRepository::class,
        );

        $this->resolver = \Mockery::mock(
            DefaultTranslationResolver::class,
        );

        $this->builder = \Mockery::mock(
            FormBuilderInterface::class,
        );

        $this->type = new DefaultTranslationsType(
            $this->localeRepository,
            $this->resolver,
        );
    }

    public function testBuildFormAddsEventListeners(): void
    {
        $locales = new ArrayCollection();

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn($locales);

        $this->builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::type(\Closure::class),
            )
            ->andReturnSelf();

        $this->builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type(\Closure::class),
            )
            ->andReturnSelf();

        $this->type->buildForm(
            $this->builder,
            [],
        );
    }

    public function testPreSetDataAddsTranslationFields(): void
    {
        $de = \Mockery::mock(Locale::class);
        $de
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $es = \Mockery::mock(Locale::class);
        $es
            ->shouldReceive('getId')
            ->once()
            ->andReturn('es_ES');

        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class,
        );

        $deTranslation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );
        $deTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Standardwert');

        $esTranslation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );
        $esTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Valor predeterminado');

        $this->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(
                $configuration,
                $de,
            )
            ->andReturn($deTranslation);

        $this->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(
                $configuration,
                $es,
            )
            ->andReturn($esTranslation);

        $parent = \Mockery::mock(FormInterface::class);
        $parent
            ->shouldReceive('getData')
            ->once()
            ->andReturn($configuration);

        $form = \Mockery::mock(FormInterface::class);
        $form
            ->shouldReceive('getParent')
            ->once()
            ->andReturn($parent);

        $form
            ->shouldReceive('add')
            ->once()
            ->with(
                'de_DE',
                TextareaType::class,
                [
                    'label' => 'de_DE',
                    'required' => false,
                    'mapped' => false,
                    'data' => 'Standardwert',
                ],
            )
            ->andReturnSelf();

        $form
            ->shouldReceive('add')
            ->once()
            ->with(
                'es_ES',
                TextareaType::class,
                [
                    'label' => 'es_ES',
                    'required' => false,
                    'mapped' => false,
                    'data' => 'Valor predeterminado',
                ],
            )
            ->andReturnSelf();

        $listener = $this->getListener(
            FormEvents::PRE_SET_DATA,
            $this->createLocaleCollection(
                $de,
                $es,
            ),
        );

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    public function testPreSetDataDoesNothingWhenParentDataIsNotConfiguration(): void
    {
        $parent = \Mockery::mock(FormInterface::class);
        $parent
            ->shouldReceive('getData')
            ->once()
            ->andReturn(null);

        $form = \Mockery::mock(FormInterface::class);
        $form
            ->shouldReceive('getParent')
            ->once()
            ->andReturn($parent);

        $form->shouldNotReceive('add');

        $this->resolver
            ->shouldNotReceive('resolve');

        $listener = $this->getListener(
            FormEvents::PRE_SET_DATA,
            new ArrayCollection(),
        );

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    public function testPreSubmitAddsMissingTranslationFields(): void
    {
        $de = \Mockery::mock(Locale::class);
        $de
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $es = \Mockery::mock(Locale::class);
        $es
            ->shouldReceive('getId')
            ->once()
            ->andReturn('es_ES');

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('has')
            ->once()
            ->with('de_DE')
            ->andReturnFalse();

        $form
            ->shouldReceive('has')
            ->once()
            ->with('es_ES')
            ->andReturnFalse();

        $form
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

        $form
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

        $this->resolver
            ->shouldNotReceive('resolve');

        $listener = $this->getListener(
            FormEvents::PRE_SUBMIT,
            $this->createLocaleCollection(
                $de,
                $es,
            ),
        );

        $listener(
            new FormEvent(
                $form,
                [
                    'de_DE' => 'Standardwert',
                    'es_ES' => 'Valor predeterminado',
                ],
            ),
        );
    }

    public function testPreSubmitDoesNotAddExistingTranslationField(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('has')
            ->once()
            ->with('de_DE')
            ->andReturnTrue();

        $form->shouldNotReceive('add');

        $this->resolver
            ->shouldNotReceive('resolve');

        $listener = $this->getListener(
            FormEvents::PRE_SUBMIT,
            $this->createLocaleCollection($locale),
        );

        $listener(
            new FormEvent(
                $form,
                [
                    'de_DE' => 'Standardwert',
                ],
            ),
        );
    }

    public function testConfigureOptionsDefinesCollectionOptions(): void
    {
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

        $this->type->configureOptions($resolver);
    }

    /**
     * @param Collection<int, Locale> $locales
     *
     * @return \Closure(FormEvent): void
     */
    private function getListener(
        string $eventName,
        Collection $locales,
    ): \Closure {
        /** @var \Closure|null $listener */
        $listener = null;

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn($locales);

        $this->builder
            ->shouldReceive('addEventListener')
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::on(
                    static function (mixed $callback) use (
                        &$listener,
                        $eventName,
                    ): bool {
                        if (!$callback instanceof \Closure) {
                            return false;
                        }

                        if (FormEvents::PRE_SET_DATA === $eventName) {
                            $listener = $callback;
                        }

                        return true;
                    },
                ),
            )
            ->once()
            ->andReturnSelf();

        $this->builder
            ->shouldReceive('addEventListener')
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::on(
                    static function (mixed $callback) use (
                        &$listener,
                        $eventName,
                    ): bool {
                        if (!$callback instanceof \Closure) {
                            return false;
                        }

                        if (FormEvents::PRE_SUBMIT === $eventName) {
                            $listener = $callback;
                        }

                        return true;
                    },
                ),
            )
            ->once()
            ->andReturnSelf();

        $this->type->buildForm(
            $this->builder,
            [],
        );

        if (!$listener instanceof \Closure) {
            $this->fail(
                \sprintf(
                    'Listener "%s" was not registered.',
                    $eventName,
                ),
            );
        }

        return $listener;
    }

    /**
     * @return Collection<int, Locale>
     */
    private function createLocaleCollection(
        Locale ...$locales,
    ): Collection {
        /** @var list<Locale> $locales */
        $locales = array_values($locales);

        return new ArrayCollection($locales);
    }
}
