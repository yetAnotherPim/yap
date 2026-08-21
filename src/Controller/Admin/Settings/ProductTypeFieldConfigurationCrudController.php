<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<ProductTypeFieldConfiguration>
 */
#[IsGranted('ROLE_ADMIN')]
class ProductTypeFieldConfigurationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        '/admin/field-type/{id}/input-type',
        name: 'admin_field_type_input_type',
        methods: ['GET']
    )]
    public function ajaxGetInputType(FieldType $fieldType): JsonResponse
    {
        return $this->json([
            'inputType' => $fieldType->getInputType()->value,
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return ProductTypeFieldConfiguration::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('fieldType', new TranslatableMessage('product_type_field_configuration.labels.field_type', [], 'admin'))
                ->setHtmlAttribute('data-title-field', 'true')
                ->setHtmlAttribute(
                    'data-controller',
                    'product-type-field-configuration-options',
                )
                ->setHtmlAttribute(
                    'data-action',
                    'change->product-type-field-configuration-options#update',
                )
                ->setHtmlAttribute(
                    'data-product-type-field-configuration-options-url-value',
                    '/admin/field-type/__ID__/input-type',
                )
                ->setHtmlAttribute(
                    'data-product-type-field-configuration-options-visible-input-types-value',
                    json_encode([
                        InputType::Select->value,
                        InputType::MultiSelect->value,
                    ], JSON_THROW_ON_ERROR),
                )
                ->setRequired(true)
                ->autocomplete(
                    // TODO: translate title
                    callback: fn (FieldType $fieldType) => $fieldType->getTitle().' ('.$fieldType->getCode().' - '.$fieldType->getInputType()->trans($this->translator).')',
                ),
            ArrayField::new('options')
                ->setFormTypeOption('row_attr', [
                    'data-field-type-options-field' => 'true',
                ]),
            TextareaField::new('defaultValue', new TranslatableMessage('product_type_field_configuration.labels.default_value', [], 'admin'))
                // TODO: render as tooltip
                ->setHelp(
                    new TranslatableMessage(
                        'product_type_field_configuration.default_value.help',
                        [],
                        'admin'
                    )
                ),
            BooleanField::new('required', new TranslatableMessage('product_type_field_configuration.labels.required', [], 'admin')),
            HiddenField::new('position'),
        ];
    }
}
