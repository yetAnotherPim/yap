<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type\Translation\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\State\ParameterProviderInterface;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProductRepository;

class FieldTranslationFilterProvider implements ProviderInterface
{
    //TODO: same for fields

    public function __construct(
        private ProductRepository $productRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        dd($uriVariables);

        return null;
    }
}
