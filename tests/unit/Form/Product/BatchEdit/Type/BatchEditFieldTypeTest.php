<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Type;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Type\BatchEditFieldTranslationType;
use App\Form\Product\BatchEdit\Type\BatchEditFieldType;
use App\Form\Product\Fields\Renderer\Registry\BatchEditFieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(BatchEditFieldType::class)]
class BatchEditFieldTypeTest extends MockeryTestCase
{
    private RendererRegistry&MockInterface $rendererRegistry;

    private BatchEditFieldType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rendererRegistry = \Mockery::mock(RendererRegistry::class);

        $this->type = new BatchEditFieldType(
            $this->rendererRegistry,
        );
    }

    public function testBuildFormAddsPreSetDataListener(): void
    {
        $builder = \Mockery::mock(FormBuilderInterface::class);

        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $this->assertIsCallable($listener);
    }

    public function testPreSetDataIgnoresInvalidData(): void
    {
        $this->rendererRegistry
            ->shouldNotReceive('getRenderer');

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $form = \Mockery::mock(FormInterface::class);

        $event = new FormEvent(
            $form,
            null,
        );

        $this->assertInstanceOf(\Closure::class, $listener);
        $listener($event);
    }

    public function testPreSetDataIgnoresFieldWithoutFieldType(): void
    {
        $this->rendererRegistry
            ->shouldNotReceive('getRenderer');

        $fieldData = \Mockery::mock(BatchEditFieldData::class);

        $fieldData
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $form = \Mockery::mock(FormInterface::class);

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $event = new FormEvent(
            $form,
            $fieldData,
        );

        $this->assertInstanceOf(\Closure::class, $listener);
        $listener($event);
    }

    public function testPreSetDataIgnoresRendererWithoutBatchEditSupport(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldData = \Mockery::mock(BatchEditFieldData::class);

        $fieldData
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $renderer = \Mockery::mock(FieldRendererInterface::class);

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($fieldType)
            ->andReturn($renderer);

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldNotReceive('add');

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $event = new FormEvent(
            $form,
            $fieldData,
        );

        $this->assertInstanceOf(\Closure::class, $listener);
        $listener($event);
    }

    public function testPreSetDataAddsValueField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldData = \Mockery::mock(BatchEditFieldData::class);

        $fieldData
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $renderer = \Mockery::mock(
            FieldRendererInterface::class,
            BatchEditFieldRendererInterface::class,
        );

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($fieldType)
            ->andReturn($renderer);

        $renderer
            ->shouldReceive('getBatchEditFormType')
            ->once()
            ->andReturn('Symfony\Component\Form\Extension\Core\Type\TextType');

        $batchEditOptions = [
            'attr' => [
                'class' => 'batch-edit-value',
            ],
        ];

        $renderer
            ->shouldReceive('getBatchEditOptions')
            ->once()
            ->andReturn($batchEditOptions);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturn(false);

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('add')
            ->once()
            ->with(
                'value',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                [
                    'required' => false,
                    'label' => 'general.labels.value',
                    'translation_domain' => 'admin',
                    'attr' => [
                        'class' => 'batch-edit-value',
                    ],
                ],
            )
            ->andReturn($form);

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $event = new FormEvent(
            $form,
            $fieldData,
        );

        $this->assertInstanceOf(\Closure::class, $listener);
        $listener($event);
    }

    public function testPreSetDataAddsTranslationsForTranslatableField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldData = \Mockery::mock(BatchEditFieldData::class);

        $fieldData
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $renderer = \Mockery::mock(
            FieldRendererInterface::class,
            BatchEditFieldRendererInterface::class,
        );

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($fieldType)
            ->andReturn($renderer);

        $formType = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';

        $renderer
            ->shouldReceive('getBatchEditFormType')
            ->once()
            ->andReturn($formType);

        $batchEditOptions = [
            'attr' => [
                'rows' => 4,
            ],
        ];

        $renderer
            ->shouldReceive('getBatchEditOptions')
            ->once()
            ->andReturn($batchEditOptions);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturn(true);

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('add')
            ->once()
            ->with(
                'value',
                $formType,
                [
                    'required' => false,
                    'label' => 'general.labels.value',
                    'translation_domain' => 'admin',
                    'attr' => [
                        'rows' => 4,
                    ],
                ],
            )
            ->andReturn($form);

        $form
            ->shouldReceive('add')
            ->once()
            ->with(
                'translations',
                CollectionType::class,
                [
                    'entry_type' => BatchEditFieldTranslationType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => false,
                    'entry_options' => [
                        'label' => false,
                        'form_type' => $formType,
                        'form_options' => $batchEditOptions,
                    ],
                    'prototype_name' => '__translation__',
                ],
            )
            ->andReturn($form);

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SET_DATA,
                \Mockery::capture($listener),
            );

        $this->type->buildForm($builder, []);

        $event = new FormEvent(
            $form,
            $fieldData,
        );

        $this->assertInstanceOf(\Closure::class, $listener);
        $listener($event);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        $this->assertSame(
            BatchEditFieldData::class,
            $options['data_class'],
        );
    }
}
