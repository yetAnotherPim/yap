<?php

declare(strict_types=1);

namespace App\Form\Product\Columns\Composer;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\User\User;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductColumnsComposer
{
    public function __construct(
        private readonly ProductColumnConfigurationRepository $productColumnConfigurationRepository,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return list<FieldInterface>
     */
    public function compose(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return [];
        }

        $fields = [];

        foreach ($this->productColumnConfigurationRepository->getColumnsForUser($user) as $column) {
            $fieldType = $column->getFieldType();
            $alias = $this->getFieldAlias($fieldType);

            // TODO: use existing renderer
            $fields[] = TextField::new(
                $alias,
                $fieldType->getTranslatedTitleByLocale(
                    $this->translator->getLocale(),
                ),
            )
                ->setValue('')
                ->setSortable(true)
                ->formatValue(
                    static function (
                        mixed $value,
                        Product $product,
                    ) use ($fieldType): ?string {
                        foreach ($product->getFields() as $field) {
                            if (!$field instanceof Field) {
                                continue;
                            }

                            if ($field->getType()->getId()->equals($fieldType->getId())) {
                                return $field->getValue();
                            }
                        }

                        return null;
                    },
                );
        }

        return $fields;
    }

    public function composeQuery(
        QueryBuilder $queryBuilder,
        SearchDto $searchDto,
    ): QueryBuilder {
        $user = $this->security->getUser();

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
