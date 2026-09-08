<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Synchronizer\Extractor;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;

class FieldValueExtractor
{
    /**
     * @param array<mixed>         $requestData
     * @param array<string, mixed> $uploadedFilePaths
     */
    public function extract(FieldType $fieldType, array $requestData, array $uploadedFilePaths = [], ?Locale $locale = null): ?string
    {
        $formFieldName = 'field_'.$fieldType->getCode()
            .(null === $locale ? '' : '-'.$locale->getId());

        if (InputType::CheckBox === $fieldType->getInputType()) {
            return (string) \array_key_exists($formFieldName, $requestData);
        }

        if (InputType::File === $fieldType->getInputType() || InputType::Image === $fieldType->getInputType()) {
            return isset($uploadedFilePaths[$formFieldName])
                // @phpstan-ignore-next-line
                ? (string) $uploadedFilePaths[$formFieldName]
                : null;
        }

        /** @phpstan-var array<int,string>|string|int|bool|float|null $value */
        $value = $requestData[$formFieldName] ?? null;

        if (InputType::MultiSelect === $fieldType->getInputType()) {
            /** @phpstan-var array<int,string>|null $value */
            return (null !== $value) ? implode(',', $value) : null;
        }

        if (true === \is_array($value)) {
            return '';
        }

        return '' === $value ? null : (string) $value;
    }
}
