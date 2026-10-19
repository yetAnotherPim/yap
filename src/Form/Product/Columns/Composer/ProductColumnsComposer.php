<?php

declare(strict_types=1);

namespace App\Form\Product\Columns\Composer;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Form\Product\Columns\Renderer\ColumnValueRenderer;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductColumnsComposer
{
    public function __construct(
        private readonly ProductColumnConfigurationRepository $productColumnConfigurationRepository,
        private readonly TranslatorInterface $translator,
        private readonly ColumnValueRenderer $columnValueRenderer,
    ) {
    }

    /**
     * @return list<FieldInterface>
     */
    public function compose(?User $user): array
    {
        if (null === $user) {
            return [];
        }

        $fields = [
            CollectionField::new('identifiers', new TranslatableMessage('product.labels.identifiers', [], 'admin'))
                ->formatValue(
                    fn (Collection $identifiers): string => $identifiers
                        ->matching(
                            Criteria::create()
                                ->orderBy(['type.title' => Order::Ascending])
                        )
                        ->reduce(
                            /** @phpstan-ignore-next-line */
                            static fn (string $formattedValue, Identifier $identifier): string => $formattedValue .= '<b>'.$identifier->getType()->getTitle().':</b> '.$identifier->getValue().'<br/>',
                            ''
                        )
                ),
            AssociationField::new('productType', new TranslatableMessage('product.labels.product_type', [], 'admin'))
                ->formatValue(
                    fn (ProductType $productType): string => $productType->getTranslatedTitleByLocale($this->translator->getLocale())
                ),
        ];

        foreach ($this->productColumnConfigurationRepository->getColumnsForUser($user) as $column) {
            $fieldType = $column->getFieldType();
            $alias = $this->getFieldAlias($fieldType);

            $fields[] = TextField::new(
                $alias,
                $fieldType->getTranslatedTitleByLocale(
                    $this->translator->getLocale(),
                ),
            )
                ->setValue('')
                ->setSortable(true)
                ->formatValue(
                    fn (mixed $value, Product $product): ?string => $this->columnValueRenderer->render(
                        $product,
                        $fieldType,
                    ),
                );
        }

        $fields[] = DateTimeField::new(
            'createdAt',
            new TranslatableMessage('product.labels.created_at', [], 'admin'),
        );

        $fields[] = DateTimeField::new(
            'updatedAt',
            new TranslatableMessage('product.labels.updated_at', [], 'admin'),
        );

        return $fields;
    }

    public function composeQuery(
        QueryBuilder $queryBuilder,
        SearchDto $searchDto,
        ?User $user,
    ): QueryBuilder {
        if (!$user instanceof User) {
            return $queryBuilder;
        }

        /**
         * @var array<string, string> $sortableAliases
         */
        $sortableAliases = [];

        foreach ($this->productColumnConfigurationRepository->getColumnsForUser($user) as $column) {
            $fieldType = $column->getFieldType();
            $alias = $this->getFieldAlias($fieldType);
            $joinAlias = 'index_'.$alias;

            $sortableAliases[$alias] = $joinAlias;

            $queryBuilder
                ->leftJoin(
                    'entity.fields',
                    $joinAlias,
                    'WITH',
                    $joinAlias.'.type = :'.$alias.'_type',
                )
                ->setParameter(
                    $alias.'_type',
                    $fieldType,
                )
                ->addSelect($joinAlias);
        }

        $sort = $searchDto->getSort();

        if ([] !== $sort) {
            $queryBuilder->resetDQLPart('orderBy');

            foreach ($sort as $property => $direction) {
                if (isset($sortableAliases[$property])) {
                    $queryBuilder->addOrderBy(
                        $sortableAliases[$property].'.value',
                        $direction,
                    );

                    continue;
                }

                $queryBuilder->addOrderBy(
                    'entity.'.$property,
                    $direction,
                );
            }
        }

        return $queryBuilder;
    }

    private function getFieldAlias(FieldType $fieldType): string
    {
        return 'field_'.str_replace(
            '-',
            '_',
            (string) $fieldType->getId(),
        );
    }
}
