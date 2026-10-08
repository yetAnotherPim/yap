<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Type\BatchEditFieldType;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(BatchEditType::class)]
class BatchEditTypeTest extends MockeryTestCase
{
    private TranslatorInterface&MockInterface $translator;

    private BatchEditType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = \Mockery::mock(TranslatorInterface::class);

        $this->type = new BatchEditType($this->translator);
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

        $this->type->buildForm($builder, []);

        $this->assertIsCallable($choiceFilter);
        $this->assertFalse($choiceFilter(null));
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
}
