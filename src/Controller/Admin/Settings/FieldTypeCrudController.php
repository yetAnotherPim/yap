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

/**
 * @extends AbstractCrudController<FieldType>
 */
#[IsGranted('ROLE_ADMIN')]
class FieldTypeCrudController extends AbstractCrudController
{
    public function __construct(private readonly LocaleRepository $localeRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return FieldType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset('Field Type'),
            TextField::new('code'),
            TextField::new('title'),
            BooleanField::new('translatable'),
            BooleanField::new('filterable'),
            ChoiceField::new('inputType')
                ->setChoices(InputType::cases())
                ->setFormTypeOption('attr', [
                    'data-controller' => 'field-type-input-type',
                    'data-action' => 'change->field-type-input-type#update',
                    'data-field-type-input-type-number-value' => InputType::Number->value,
                ]),
            TextField::new('decimalSeparator')
                ->setEmptyData()
                ->setMaxLength(1)
                ->onlyOnForms(),
            FormField::addColumn(6),
            FormField::addFieldset('Translation'),
            LocaleField::new('translation_locale')
                ->includeOnly($this->localeRepository->getAvailableLocaleIds())
                ->setFormTypeOptions([
                    'label' => 'Language',
                    'data' => '',
                    'mapped' => false,
                ]),
            // TODO: for all locales
            TextField::new('translation_title')
                ->setFormTypeOptions([
                    'label' => 'Title',
                    'data' => '',
                    'mapped' => false,
                ]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->autocomplete(
                callback: static fn (FieldType $fieldType) => $fieldType->getTitle().' ('.$fieldType->getCode().' - '.$fieldType->getInputType()->name.')',
            )
        ;
    }
}
