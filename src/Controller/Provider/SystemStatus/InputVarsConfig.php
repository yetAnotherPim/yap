<?php

declare(strict_types=1);

namespace App\Controller\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Entity\Product\Type\ProductType;
use App\Repository\LocaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatableMessage;

class InputVarsConfig implements SystemCheckInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LocaleRepository $localeRepository)
    {
    }

    /**
     * @return list<TranslatableMessage>
     */
    public function check(): array
    {
        $messages = [];
        $maxInputVars = (int) \ini_get('max_input_vars');
        $maxInputVars = 10;

        $enabledLocales = $this->localeRepository->count([
            'contentTranslationEnabled' => true,
        ]);

        foreach ($this->getTotalFieldCounts() as $productType) {
            $inputVars = $productType['fieldCount']
                + ($productType['translatableFieldCount'] * $enabledLocales);

            if ($inputVars <= $maxInputVars - 10) {
                continue;
            }

            $messages[] =
                new SystemStatusMessage(
                    MessageType::Warning,
                    new TranslatableMessage(
                        'dashboard.system_status.input_vars.message',
                        [
                            '%productType%' => $productType['title'],
                            '%fieldCount%' => $inputVars,
                            '%maxInputVars%' => $maxInputVars,
                        ],
                        'admin',
                    )
                );
        }

        return $messages;
    }

    /**
     * @return list<array{
     *     code: string,
     *     title: string,
     *     fieldCount: int,
     *     translatableFieldCount: int
     * }>
     */
    private function getTotalFieldCounts(): array
    {
        $rows = $this->entityManager
            ->createQueryBuilder()
            ->select('pt.code AS code')
            ->addSelect('pt.title AS title')
            ->addSelect('COUNT(c.id) AS fieldCount')
            ->addSelect(
                'SUM(CASE WHEN ft.translatable = true THEN 1 ELSE 0 END) AS translatableFieldCount'
            )
            ->from(ProductType::class, 'pt')
            ->leftJoin('pt.productTypeFieldConfigurations', 'c')
            ->leftJoin('c.fieldType', 'ft')
            ->groupBy('pt.id')
            ->addGroupBy('pt.code')
            ->addGroupBy('pt.title')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): array => [
                'code' => $row['code'],
                'title' => $row['title'],
                'fieldCount' => (int) $row['fieldCount'],
                'translatableFieldCount' => (int) $row['translatableFieldCount'],
            ],
            $rows,
        );
    }
}
