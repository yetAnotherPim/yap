<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Type;

use App\Entity\Locale\Locale;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use App\Form\Product\BatchEdit\Type\BatchEditFieldTranslationType;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(BatchEditFieldTranslationType::class)]
class BatchEditFieldTranslationTypeTest extends MockeryTestCase
{
    private LocaleRepository&MockInterface $localeRepository;

    private BatchEditFieldTranslationType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeRepository = \Mockery::mock(LocaleRepository::class);

        $this->type = new BatchEditFieldTranslationType(
            $this->localeRepository,
        );
    }

    public function testBuildForm(): void
    {
        $locales = [
            \Mockery::mock(Locale::class),
            \Mockery::mock(Locale::class),
        ];

        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(
                new \Doctrine\Common\Collections\ArrayCollection($locales),
            );

        $builder = \Mockery::mock(FormBuilderInterface::class);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'locale',
                EntityType::class,
                [
                    'class' => Locale::class,
                    'choices' => $locales,
                    'choice_label' => 'id',
                    'label' => 'general.labels.locale',
                    'translation_domain' => 'admin',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'form-select',
                    ],
                ],
            )
            ->andReturn($builder);

        $formOptions = [
            'attr' => [
                'class' => 'batch-edit-textarea',
            ],
        ];

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'value',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => 'general.labels.value',
                    'translation_domain' => 'admin',
                    'attr' => [
                        'class' => 'batch-edit-textarea',
                    ],
                ],
            )
            ->andReturn($builder);

        $this->type->buildForm(
            $builder,
            [
                'form_type' => TextareaType::class,
                'form_options' => $formOptions,
            ],
        );
    }

    public function testBuildFormPassesFormOptionsToValueField(): void
    {
        $this->localeRepository
            ->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new \Doctrine\Common\Collections\ArrayCollection());

        $builder = \Mockery::mock(FormBuilderInterface::class);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'locale',
                EntityType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'value',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => 'general.labels.value',
                    'translation_domain' => 'admin',
                    'attr' => [
                        'rows' => 4,
                        'class' => 'batch-edit-textarea',
                    ],
                ],
            )
            ->andReturn($builder);

        $this->type->buildForm(
            $builder,
            [
                'form_type' => TextareaType::class,
                'form_options' => [
                    'attr' => [
                        'rows' => 4,
                        'class' => 'batch-edit-textarea',
                    ],
                ],
            ],
        );
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $options = $resolver->resolve([
            'form_type' => TextareaType::class,
            'form_options' => [],
        ]);

        $this->assertSame(
            BatchEditFieldTranslationData::class,
            $options['data_class'],
        );
        $this->assertSame(
            TextareaType::class,
            $options['form_type'],
        );
        $this->assertSame(
            [],
            $options['form_options'],
        );
    }

    public function testConfigureOptionsRequiresFormType(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $this->expectException(\Symfony\Component\OptionsResolver\Exception\MissingOptionsException::class);

        $resolver->resolve([
            'form_options' => [],
        ]);
    }

    public function testConfigureOptionsRequiresFormOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $this->expectException(\Symfony\Component\OptionsResolver\Exception\MissingOptionsException::class);

        $resolver->resolve([
            'form_type' => TextareaType::class,
        ]);
    }

    public function testConfigureOptionsRejectsInvalidFormType(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        $resolver->resolve([
            'form_type' => 123,
            'form_options' => [],
        ]);
    }

    public function testConfigureOptionsRejectsInvalidFormOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        $resolver->resolve([
            'form_type' => TextareaType::class,
            'form_options' => 'invalid',
        ]);
    }
}
