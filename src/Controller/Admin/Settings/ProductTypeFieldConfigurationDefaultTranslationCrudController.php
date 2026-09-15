<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<ProductTypeFieldConfigurationDefaultTranslation>
 */
#[IsGranted('ROLE_ADMIN')]
class ProductTypeFieldConfigurationDefaultTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductTypeFieldConfigurationDefaultTranslation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('locale', new TranslatableMessage('locale.entity.title', [], 'admin'))
                ->setQueryBuilder(
                    static fn (QueryBuilder $queryBuilder): QueryBuilder => $queryBuilder
                        ->andWhere('entity.contentTranslationEnabled = :enabled')
                        ->setParameter('enabled', true),
                )
                ->setFormTypeOption(
                    'choice_label',
                    static fn (Locale $locale): string => $locale->getId(),
                ),
            TextareaField::new('value', new TranslatableMessage('product_type_field_configuration.labels.default_value', [], 'admin')),
        ];
    }
}
