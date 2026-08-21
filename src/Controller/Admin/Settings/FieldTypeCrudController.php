<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<FieldType>
 */
#[IsGranted('ROLE_ADMIN')]
class FieldTypeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return FieldType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset(new TranslatableMessage('field_type.entity.title', [], 'admin')),
            TextField::new('code', new TranslatableMessage('field_type.labels.code', [], 'admin')),
            TextField::new('title', new TranslatableMessage('field_type.labels.title', [], 'admin')),
            BooleanField::new('translatable', new TranslatableMessage('field_type.labels.translatable', [], 'admin')),
            BooleanField::new('filterable', new TranslatableMessage('field_type.labels.filterable', [], 'admin')),
            ChoiceField::new('inputType', new TranslatableMessage('field_type.labels.input_type', [], 'admin'))
                ->setFormTypeOption('attr', [
                    'data-controller' => 'field-type-input-type',
                    'data-action' => 'change->field-type-input-type#update',
                    'data-field-type-input-type-number-value' => InputType::Number->value,
                ]),
            TextField::new('decimalSeparator', new TranslatableMessage('field_type.labels.decimal_separator', [], 'admin'))
                ->setEmptyData()
                ->setMaxLength(1)
                ->onlyOnForms(),
            FormField::addColumn(6),
            FormField::addFieldset(new TranslatableMessage('general.labels.translation', [], 'admin')),
            LocaleField::new('translation_locale', new TranslatableMessage('general.labels.locale', [], 'admin'))
                ->includeOnly($this->localeRepository->getAvailableLocaleIds())
                ->setFormTypeOptions([
                    'label' => 'Language',
                    'data' => '',
                    'mapped' => false,
                ])
                ->onlyOnForms(),
            // TODO: for all locales
            TextField::new('translation_title')
                ->setFormTypeOptions([
                    'label' => 'Title',
                    'data' => '',
                    'mapped' => false,
                ])
                ->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('field_type.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('field_type.entity.title_plural', [], 'admin'))
            ->autocomplete(
                // TODO: translate title
                callback: fn (FieldType $fieldType) => $fieldType->getTitle().' ('.$fieldType->getCode().' - '.$fieldType->getInputType()->trans($this->translator).')',
            )
        ;
    }
}
