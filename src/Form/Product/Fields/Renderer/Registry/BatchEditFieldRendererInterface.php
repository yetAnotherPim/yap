<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Registry;

use App\Entity\Product\Field\Type\FieldType;

interface BatchEditFieldRendererInterface
{
    public function getBatchEditFormType(
        FieldType $fieldType,
    ): string;
}
