<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Registry;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class FieldFilterHandlerRegistry
{
    /**
     * @var Collection<int, FieldFilterHandlerInterface>
     */
    private Collection $handlers;

    /**
     * @param iterable<FieldFilterHandlerInterface> $handlers
     */
    public function __construct(
        #[AutowireIterator('form.product.filter.field.handler')]
        iterable $handlers,
    ) {
        $this->handlers = new ArrayCollection(
            iterator_to_array($handlers),
        );
    }

    public function get(
        FieldType $fieldType,
    ): FieldFilterHandlerInterface {
        $handler = $this->handlers->findFirst(
            static fn (
                int $key,
                FieldFilterHandlerInterface $handler,
            ): bool => $handler->supports($fieldType),
        );

        if (null === $handler) {
            throw new \LogicException(\sprintf('No filter handler supports input type "%s".', $fieldType->getInputType()->name));
        }

        return $handler;
    }
}
