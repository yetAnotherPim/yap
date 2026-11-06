<?php

declare(strict_types=1);

namespace App\DemoData\Generator;

use App\DemoData\Definition\ProductDefinitions;
use App\DemoData\Definition\ProductTypeDefinitions;

class ProductVariationGenerator
{
    /**
     * @param key-of<ProductDefinitions::PRODUCTS> $typeCode
     *
     * @return array{
     *     values: array<string, string>,
     *     translations: array<string, array<string, string>>
     * }
     */
    public function generate(string $typeCode): array
    {
        $definition = ProductDefinitions::PRODUCTS[$typeCode];

        $product = $definition['products'][array_rand($definition['products'])];

        /** @var array<string, string> $values */
        $values = array_replace($definition['defaults']['values'], $product['values']);

        /** @var array<string, array<string, string>> $translations */
        $translations = array_replace_recursive(
            $definition['defaults']['translations'],
            $product['translations'],
        );

        foreach ($product['variations'] ?? [] as $fieldCode) {
            /** @var 'apparel' $typeCode */
            /** @var 'colour'|'size'|'fit' $fieldCode */
            $options = ProductTypeDefinitions::PRODUCT_TYPES[$typeCode]['fields'][$fieldCode]['options'];

            $value = $options[array_rand($options)];
            $values[$fieldCode] = $value;

            $valueTranslations = $definition['variation_translations'][$fieldCode][$value] ?? [];

            foreach ($valueTranslations as $localeCode => $translation) {
                $translations[$fieldCode][$localeCode] = $translation;
            }

            if (\in_array($fieldCode, $product['name_suffix_fields'] ?? [], true)) {
                $values['name'] .= ' '.$value;

                foreach ($translations['name'] ?? [] as $localeCode => &$name) {
                    $name .= ' '.($valueTranslations[$localeCode] ?? $value);
                }

                unset($name);
            }
        }

        return [
            'values' => $values,
            'translations' => $translations,
        ];
    }
}
