<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ProductFieldsRenderer
{
    public function __construct(private readonly RendererRegistry $rendererRegistry)
    {
    }

    /**
     * @return array<FieldInterface>
     */
    public function render(Product $product): array
    {
        $fields = [];

        $fields[] = FormField::addTab('Fields');

        foreach ($product->getProductType()->getProductTypeFieldConfigurations() as $productTypeFieldConfiguration) {
            if (null === $productTypeFieldConfiguration->getFieldType()) {
                continue;
            }

            // TODO: get field from product, if null create with default value
            $field = new Field()
                ->setType($productTypeFieldConfiguration->getFieldType());

            $fields[] = $this->rendererRegistry
                ->getRenderer($field)
                ->render($field);
        }

        //TODO: save

        return $fields;
    }
}
