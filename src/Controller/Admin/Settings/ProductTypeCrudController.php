<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<ProductType>
 */
#[IsGranted('ROLE_ADMIN')]
class ProductTypeCrudController extends AbstractCrudController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ProductType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('product_type.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('product_type.entity.title_plural', [], 'admin'))
            ->setSearchFields([
                'title',
                'code',
                'titleTranslations.value',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('code', new TranslatableMessage('product_type.labels.code', [], 'admin')),
                TextField::new('title', new TranslatableMessage('product_type.labels.title', [], 'admin')),
            ];
        }

        return [
            TextField::new('code', new TranslatableMessage('product_type.labels.code', [], 'admin')),
            TextField::new('title', new TranslatableMessage('product_type.labels.title', [], 'admin'))
                ->formatValue(
                    fn (?string $value, FieldType $fieldType): ?string => $fieldType->getTranslatedTitle($this->translator->getLocale()),
                ),
            FormField::addFieldset(new TranslatableMessage('product_type.labels.fields', [], 'admin')),
            CollectionField::new('productTypeFieldConfigurations')
                ->setRequired(true)
                ->setEntryIsComplex()
                ->setLabel(false)
                ->addCssClass('sortable-collection')
                ->setEntryToStringMethod(
                    fn (ProductTypeFieldConfiguration $productTypeFieldConfiguration): string => (null !== $productTypeFieldConfiguration->getFieldType())
                        ? $productTypeFieldConfiguration->getFieldType()->getTranslatedTitle(
                            $this->translator->getLocale(),
                        ).' ('.$productTypeFieldConfiguration->getFieldType()->getCode().' - '.$productTypeFieldConfiguration->getFieldType()->getInputType()->trans($this->translator).')'
                        : ''
                ),
        ];
    }
}
