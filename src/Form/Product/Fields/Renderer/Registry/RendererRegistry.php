<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Registry;

use App\Entity\Product\Field\Field;
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

    public function getRenderer(Field $field): FieldRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($field->getType())) {
                return $renderer;
            }
        }

        throw new \LogicException(\sprintf('No renderer for "%s"', $field->getType()->getInputType()->name));
    }
}
