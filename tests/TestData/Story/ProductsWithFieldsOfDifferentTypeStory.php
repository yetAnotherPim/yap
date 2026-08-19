<?php

declare(strict_types=1);

namespace App\Tests\TestData\Story;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class ProductsWithFieldsOfDifferentTypeStory extends Story
{
    public function build(): void
    {
        $product = ProductFactory::createOne([
            'fields' => [
                FieldFactory::new(['value' => null, 'type' => FieldTypeFactory::new(['code' => 'null_value', 'inputType' => InputType::Text])]),
                FieldFactory::new(['value' => '', 'type' => FieldTypeFactory::new(['code' => 'empty_string', 'inputType' => InputType::Text])]),
                FieldFactory::new(['value' => 'Something', 'type' => FieldTypeFactory::new(['code' => 'string', 'inputType' => InputType::Text])]),
                FieldFactory::new(['value' => '1', 'type' => FieldTypeFactory::new(['code' => 'checkbox_true', 'inputType' => InputType::CheckBox])]),
                FieldFactory::new(['value' => '', 'type' => FieldTypeFactory::new(['code' => 'checkbox_false', 'inputType' => InputType::CheckBox])]),
                FieldFactory::new(['value' => null, 'type' => FieldTypeFactory::new(['code' => 'checkbox_null', 'inputType' => InputType::CheckBox])]),
                FieldFactory::new(['value' => 'something', 'type' => FieldTypeFactory::new(['code' => 'checkbox_unexpected', 'inputType' => InputType::CheckBox])]),
                FieldFactory::new(['value' => '1', 'type' => FieldTypeFactory::new(['code' => 'number_1', 'inputType' => InputType::Number])]),
                FieldFactory::new(['value' => '3.14159265359', 'type' => FieldTypeFactory::new(['code' => 'number_float', 'inputType' => InputType::Number])]),
                FieldFactory::new(['value' => '', 'type' => FieldTypeFactory::new(['code' => 'number_empty_string', 'inputType' => InputType::Number])]),
                FieldFactory::new(['value' => null, 'type' => FieldTypeFactory::new(['code' => 'number_null', 'inputType' => InputType::Number])]),
                FieldFactory::new(['value' => 'something', 'type' => FieldTypeFactory::new(['code' => 'number_unexpected', 'inputType' => InputType::Number])]),
                FieldFactory::new(['value' => '1', 'type' => FieldTypeFactory::new(['code' => 'integer_1', 'inputType' => InputType::Integer])]),
                FieldFactory::new(['value' => '3.14159265359', 'type' => FieldTypeFactory::new(['code' => 'integer_float', 'inputType' => InputType::Integer])]),
                FieldFactory::new(['value' => '', 'type' => FieldTypeFactory::new(['code' => 'integer_empty_string', 'inputType' => InputType::Integer])]),
                FieldFactory::new(['value' => null, 'type' => FieldTypeFactory::new(['code' => 'integer_null', 'inputType' => InputType::Integer])]),
                FieldFactory::new(['value' => 'something', 'type' => FieldTypeFactory::new(['code' => 'integer_unexpected', 'inputType' => InputType::Integer])]),
            ],
        ]
        );

        $this->addState('product', $product);
    }
}
