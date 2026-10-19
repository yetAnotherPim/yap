<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Registry;

use App\Entity\Product\Field\Type\FieldType;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class RendererRegistry
{
    /**
     * @param iterable<FieldRendererInterface> $renderers
     */
    public function __construct(
        #[AutowireIterator('form.product.fields.renderer')]
        private readonly iterable $renderers,
    ) {
    }

    public function getRenderer(FieldType $fieldType): FieldRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($fieldType)) {
                return $renderer;
            }
        }

        throw new \LogicException(\sprintf('No renderer for "%s"', $fieldType->getInputType()->name));
    }
}
