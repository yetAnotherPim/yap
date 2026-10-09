<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Type\BatchEditFieldType;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(BatchEditType::class)]
#[UsesClass(BatchEditFieldData::class)]
class BatchEditTypeTest extends MockeryTestCase
{
    private TranslatorInterface&MockInterface $translator;

    private EntityManagerInterface&MockInterface $entityManager;

    private BatchEditType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);

        $this->type = new BatchEditType($this->translator, $this->entityManager);
    }

    public function testBuildForm(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);

        $fieldTypeOptions = null;
        $fieldsOptions = null;

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::on(
                    function (array $options) use (&$fieldTypeOptions): bool {
                        $fieldTypeOptions = $options;

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::on(
                    function (array $options) use (&$fieldsOptions): bool {
                        $fieldsOptions = $options;

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type('callable'),
                100,
            )
            ->andReturn($builder);

        $this->type->buildForm($builder, []);

        $this->assertIsArray($fieldTypeOptions);
        $this->assertSame(FieldType::class, $fieldTypeOptions['class']);
        $this->assertFalse($fieldTypeOptions['mapped']);
        $this->assertFalse($fieldTypeOptions['required']);

        $this->assertIsArray($fieldsOptions);
        $this->assertSame(BatchEditFieldType::class, $fieldsOptions['entry_type']);
        $this->assertTrue($fieldsOptions['allow_add']);
        $this->assertTrue($fieldsOptions['allow_delete']);
        $this->assertFalse($fieldsOptions['by_reference']);
        $this->assertFalse($fieldsOptions['required']);
        $this->assertFalse($fieldsOptions['label']);
        $this->assertSame(
            ['label' => false],
            $fieldsOptions['entry_options'],
        );
    }

    public function testFieldTypeChoiceLabelUsesTranslatorLocale(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);

        /** @var callable(FieldType): string|null $choiceLabel */
        $choiceLabel = null;

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::on(
                    function (array $options) use (&$choiceLabel): bool {
                        $choiceLabel = $options['choice_label'];

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type('callable'),
                100,
            )
            ->andReturn($builder);

        $this->type->buildForm($builder, []);

        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de')
            ->andReturn('Lagerbestand');

        $this->assertIsCallable($choiceLabel);
        $this->assertSame(
            'Lagerbestand',
            $choiceLabel($fieldType),
        );
    }

    public function testFieldTypeChoiceFilterExcludesSection(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);

        /** @var callable(FieldType): string|null $choiceFilter */
        $choiceFilter = null;

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::on(
                    function (array $options) use (&$choiceFilter): bool {
                        $choiceFilter = $options['choice_filter'];

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type('callable'),
                100,
            )
            ->andReturn($builder);

        $this->type->buildForm($builder, []);

        $section = \Mockery::mock(FieldType::class);

        $section
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Section);

        $this->assertIsCallable($choiceFilter);
        $this->assertFalse($choiceFilter($section));
    }

    public function testFieldTypeChoiceFilterAcceptsNonSection(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);

        /** @var callable(FieldType): string|null $choiceFilter */
        $choiceFilter = null;

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::on(
                    function (array $options) use (&$choiceFilter): bool {
                        $choiceFilter = $options['choice_filter'];

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type('callable'),
                100,
            )
            ->andReturn($builder);

        $this->type->buildForm($builder, []);

        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $this->assertIsCallable($choiceFilter);
        $this->assertTrue($choiceFilter($fieldType));
    }

    public function testFieldTypeChoiceFilterRejectsNull(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);
        /** @var callable(FieldType): string|null $choiceFilter */
        $choiceFilter = null;

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::on(
                    function (array $options) use (&$choiceFilter): bool {
                        $choiceFilter = $options['choice_filter'];

                        return true;
                    },
                ),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::type('callable'),
                100,
            )
            ->andReturn($builder);

        $this->type->buildForm($builder, []);

        $this->assertIsCallable($choiceFilter);
        $this->assertFalse($choiceFilter(null));
    }

    public function testPreSubmitIgnoresInvalidData(): void
    {
        $this->entityManager
            ->shouldNotReceive('getRepository');

        $listener = $this->getPreSubmitListener();

        $form = \Mockery::mock(FormInterface::class);
        $form->shouldNotReceive('get');

        $event = new FormEvent($form, 'invalid');

        $listener($event);

        $this->assertSame('invalid', $event->getData());
    }

    public function testPreSubmitIgnoresMissingOrEmptyFields(): void
    {
        $this->entityManager
            ->shouldNotReceive('getRepository');

        $listener = $this->getPreSubmitListener();

        $form = \Mockery::mock(FormInterface::class);
        $form->shouldNotReceive('get');

        foreach ([
            [],
            ['fields' => null],
            ['fields' => 'invalid'],
            ['fields' => []],
        ] as $data) {
            $event = new FormEvent($form, $data);

            $listener($event);

            $this->assertSame($data, $event->getData());
        }
    }

    public function testPreSubmitAddsValidFieldsAndRemovesInvalidFields(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('name');

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $sectionFieldType = \Mockery::mock(FieldType::class);

        $sectionFieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Section);

        $repository = \Mockery::mock(EntityRepository::class);

        $repository
            ->shouldReceive('findBy')
            ->once()
            ->with([
                'code' => [
                    'name',
                    'section',
                    'unknown',
                    'malformed',
                ],
            ])
            ->andReturn([
                $fieldType,
                $sectionFieldType,
            ]);

        $this->entityManager
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($repository);

        $fieldsForm = \Mockery::mock(FormInterface::class);

        $fieldsForm
            ->shouldReceive('has')
            ->once()
            ->with('name')
            ->andReturn(false);

        $fieldsForm
            ->shouldReceive('add')
            ->once()
            ->with(
                'name',
                BatchEditFieldType::class,
                \Mockery::on(
                    static fn (array $options): bool => $options['data'] instanceof BatchEditFieldData
                        && $options['data']->getFieldType() === $fieldType
                        && false === $options['label'],
                ),
            )
            ->andReturn($fieldsForm);

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('get')
            ->once()
            ->with('fields')
            ->andReturn($fieldsForm);

        $listener = $this->getPreSubmitListener();

        $data = [
            'fields' => [
                'name' => ['value' => 'Product name'],
                'section' => ['value' => 'ignored'],
                'unknown' => ['value' => 'ignored'],
                'malformed' => 'invalid',
            ],
        ];

        $event = new FormEvent($form, $data);

        $listener($event);

        $this->assertSame([
            'fields' => [
                'name' => ['value' => 'Product name'],
            ],
        ], $event->getData());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        $this->assertSame(
            BatchEditData::class,
            $options['data_class'],
        );
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame(
            'batch_edit',
            $this->type->getBlockPrefix(),
        );
    }

    private function getPreSubmitListener(): callable
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $builder = \Mockery::mock(FormBuilderInterface::class);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fieldType',
                EntityType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $builder
            ->shouldReceive('add')
            ->once()
            ->with(
                'fields',
                CollectionType::class,
                \Mockery::type('array'),
            )
            ->andReturn($builder);

        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::PRE_SUBMIT,
                \Mockery::capture($listener),
                100,
            );

        $this->type->buildForm($builder, []);

        $this->assertIsCallable($listener);

        return $listener;
    }
}
